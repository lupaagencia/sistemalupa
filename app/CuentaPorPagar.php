<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CuentaPorPagar extends Model
{
    protected $table = 'cuentas_por_pagar';
    protected $fillable = [
        'activo_id', 
        'proveedor_id', 
        'beneficiario',
        'ordentrabajo_id', 
        'costo_id', 
        'ingreso_id', 
        'numero_factura',
        'descripcion', 
        'cantidad', 
        'cantidad_entregada', 
        'valor_unitario', 
        'monto', 
        'saldo', 
        'estado', 
        'soporte',
        'fecha',
        'fecha_vencimiento',
        'comprobante_id',
        'cuenta_id',
        'iva'
    ];

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }

    public function comprobante()
    {
        return $this->belongsTo(ComprobanteContable::class, 'comprobante_id');
    }

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function orden()
    {
        return $this->belongsTo(Ordentrabajo::class, 'ordentrabajo_id');
    }

    public function costo()
    {
        return $this->belongsTo(CostoProduccion::class, 'costo_id');
    }

    public function ingreso()
    {
        return $this->belongsTo(Ingreso::class, 'ingreso_id');
    }

    public function abonos()
    {
        return $this->hasMany(AbonoCuentaPorPagar::class, 'cuenta_por_pagar_id');
    }

    protected static function boot()
    {
        parent::boot();
    }

    protected static $isCrossing = false;

    public static function cruzarSaldosAFavor($activo_id = null, $proveedor_id = null)
    {
        if (!$activo_id && !$proveedor_id) {
            return;
        }

        if (static::$isCrossing) {
            return;
        }

        static::$isCrossing = true;

        try {
            // 1. Recalcular y sincronizar saldos para cuentas regulares (monto > 0)
            $queryCuentasReg = static::where(function($q) {
                $q->where('monto', '>', 0)
                  ->where(function($q2) {
                      $q2->whereNull('numero_factura')
                         ->orWhere('numero_factura', '!=', 'ANTICIPO');
                  });
            });

            if ($activo_id) {
                $queryCuentasReg->where('activo_id', $activo_id);
            } else {
                $queryCuentasReg->where('proveedor_id', $proveedor_id);
            }

            $cuentasReg = $queryCuentasReg->get();
            foreach ($cuentasReg as $cr) {
                $abonosReales = $cr->abonos()
                    ->whereRaw('LOWER(metodo_pago) != ?', ['saldo a favor'])
                    ->sum('monto');

                $abonosCruzados = $cr->abonos()
                    ->whereRaw('LOWER(metodo_pago) = ?', ['saldo a favor'])
                    ->sum('monto');

                $totalAbonos = (float)$abonosReales + (float)$abonosCruzados;
                $nuevoSaldo = (float)$cr->monto - $totalAbonos;

                if ($cr->saldo != $nuevoSaldo || ($nuevoSaldo <= 0 && $cr->estado !== 'Pagado') || ($nuevoSaldo > 0 && $cr->estado === 'Pagado')) {
                    $cr->saldo = $nuevoSaldo;
                    if ($nuevoSaldo <= 0) {
                        $cr->saldo = $nuevoSaldo; // 0 si está pagada, negativo si hay exceso real
                        $cr->estado = ($nuevoSaldo < 0) ? 'Abonado' : 'Pagado';
                    } else {
                        $cr->estado = ($totalAbonos > 0) ? 'Abonado' : 'Pendiente';
                    }
                    $cr->save();
                }
            }

            // 2. Recalcular y sincronizar saldos para cuentas de ANTICIPO reales (monto == 0 o numero_factura == 'ANTICIPO')
            $queryAnticipos = static::where(function($q) {
                $q->where('monto', 0)
                  ->orWhere('numero_factura', 'ANTICIPO');
            });

            if ($activo_id) {
                $queryAnticipos->where('activo_id', $activo_id);
            } else {
                $queryAnticipos->where('proveedor_id', $proveedor_id);
            }

            $anticiposList = $queryAnticipos->get();
            foreach ($anticiposList as $ant) {
                $abonosReales = $ant->abonos()
                    ->whereRaw('LOWER(metodo_pago) != ?', ['saldo a favor'])
                    ->sum('monto');

                $obsPattern = '%Ref: Anticipo #' . $ant->id . '%';
                $abonosCruzados = AbonoCuentaPorPagar::whereRaw('LOWER(metodo_pago) = ?', ['saldo a favor'])
                    ->where('observaciones', 'LIKE', $obsPattern)
                    ->sum('monto');

                $disponible = max(0.0, (float)$abonosReales - (float)$abonosCruzados);
                $nuevoSaldo = -$disponible;

                if ($ant->saldo != $nuevoSaldo || ($nuevoSaldo == 0 && $ant->estado !== 'Pagado')) {
                    $ant->saldo = $nuevoSaldo;
                    $ant->estado = ($nuevoSaldo < 0) ? 'Abonado' : 'Pagado';
                    $ant->save();
                }
            }

            // 3. Buscar cuentas con saldo a favor (saldo < 0) para este proveedor / operaria
            $querySaldosAFavor = static::where('saldo', '<', 0);
            if ($activo_id) {
                $querySaldosAFavor->where('activo_id', $activo_id);
            } else {
                $querySaldosAFavor->where('proveedor_id', $proveedor_id);
            }
            $saldosAFavor = $querySaldosAFavor->orderBy('fecha', 'asc')->orderBy('id', 'asc')->get();

            if ($saldosAFavor->isEmpty()) {
                return;
            }

            // 4. Buscar cuentas con deudas pendientes (monto > 0, saldo > 0 y estado != 'Pagado')
            $queryDeudas = static::where('monto', '>', 0)
                ->where('saldo', '>', 0)
                ->where('estado', '!=', 'Pagado');
            if ($activo_id) {
                $queryDeudas->where('activo_id', $activo_id);
            } else {
                $queryDeudas->where('proveedor_id', $proveedor_id);
            }
            $deudas = $queryDeudas->orderBy('fecha', 'asc')->orderBy('id', 'asc')->get();

            if ($deudas->isEmpty()) {
                return;
            }

            // 5. Realizar cruce automático
            foreach ($saldosAFavor as $anticipo) {
                $saldoDisponible = abs((float)$anticipo->saldo);
                if ($saldoDisponible <= 0) {
                    continue;
                }

                foreach ($deudas as $deuda) {
                    $refNombre = 'Anticipo #' . $anticipo->id;
                    $obsText = 'Cruce automático con Saldo a Favor (Ref: ' . $refNombre . ')';

                    $abonoRealesDeuda = $deuda->abonos()
                        ->whereRaw('LOWER(metodo_pago) != ?', ['saldo a favor'])
                        ->sum('monto');

                    $abonosOtrosCrucesDeuda = $deuda->abonos()
                        ->whereRaw('LOWER(metodo_pago) = ?', ['saldo a favor'])
                        ->where('observaciones', '!=', $obsText)
                        ->sum('monto');

                    $deudaPendienteReal = max(0.0, (float)$deuda->monto - (float)$abonoRealesDeuda - (float)$abonosOtrosCrucesDeuda);
                    if ($deudaPendienteReal <= 0) {
                        continue;
                    }

                    $montoACruzar = min($saldoDisponible, $deudaPendienteReal);
                    if ($montoACruzar <= 0) {
                        continue;
                    }

                    $abonoExistente = AbonoCuentaPorPagar::where('cuenta_por_pagar_id', $deuda->id)
                        ->whereRaw('LOWER(metodo_pago) = ?', ['saldo a favor'])
                        ->where('observaciones', $obsText)
                        ->first();

                    if ($abonoExistente) {
                        if ((float)$abonoExistente->monto != (float)$montoACruzar) {
                            $abonoExistente->monto = $montoACruzar;
                            $abonoExistente->save();
                        }
                    } else {
                        $abono = new AbonoCuentaPorPagar();
                        $abono->cuenta_por_pagar_id = $deuda->id;
                        $abono->monto = $montoACruzar;
                        $abono->fecha = date('Y-m-d');
                        $abono->observaciones = $obsText;
                        $abono->metodo_pago = 'Saldo a Favor';
                        if ($anticipo->soporte) {
                            $abono->soporte = $anticipo->soporte;
                        }
                        $abono->save();
                    }

                    // Actualizar saldo y estado de la deuda
                    $totalAbonosDeuda = (float)$abonoRealesDeuda + (float)$abonosOtrosCrucesDeuda + (float)$montoACruzar;
                    $nuevoSaldoDeuda = max(0.0, (float)$deuda->monto - $totalAbonosDeuda);
                    $deuda->saldo = $nuevoSaldoDeuda;
                    if ($nuevoSaldoDeuda <= 0) {
                        $deuda->saldo = 0;
                        $deuda->estado = 'Pagado';
                    } else {
                        $deuda->estado = 'Abonado';
                    }
                    $deuda->save();

                    // Actualizar saldo y estado del anticipo
                    $abonosCruzadosAnticipo = AbonoCuentaPorPagar::whereRaw('LOWER(metodo_pago) = ?', ['saldo a favor'])
                        ->where('observaciones', 'LIKE', '%Ref: Anticipo #' . $anticipo->id . '%')
                        ->sum('monto');

                    $abonosRealesAnticipo = $anticipo->abonos()
                        ->whereRaw('LOWER(metodo_pago) != ?', ['saldo a favor'])
                        ->sum('monto');

                    $disponibleAnticipo = max(0.0, (float)$abonosRealesAnticipo - (float)$abonosCruzadosAnticipo);
                    $nuevoSaldoFavor = -$disponibleAnticipo;
                    $anticipo->saldo = $nuevoSaldoFavor;
                    $anticipo->estado = ($nuevoSaldoFavor < 0) ? 'Abonado' : 'Pagado';
                    $anticipo->save();

                    $saldoDisponible = abs($nuevoSaldoFavor);
                    if ($saldoDisponible <= 0) {
                        break;
                    }
                }
            }
        } finally {
            static::$isCrossing = false;
        }
    }
}
