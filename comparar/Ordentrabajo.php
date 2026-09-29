<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ordentrabajo extends Model
{
    protected $table = 'ordentrabajos';
    protected $fillable = [
        'idcliente',
        'articulo_id',
        'medida_final',
        'cantidad',
        'tamano',
        'medida_material',
        'impuesto',
        'valor_impuesto',
        'produccion',
        'fecha',
        'cabida',
        'detalles_diseno',
        'carpeta_cliente',
        'observaciones',
        'totalParcial',
        'descuento',
        'abono',
        'saldo',
        'total',
        'estado',
        'prioridad',
        'plancha',
        'pago',
        'fecha_entrega',
        'cantidad_entregada',
        'cantidad_original'
    ];
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    public function articulo()
    {
        return $this->belongsTo(Articulo::class , 'articulo_id');
    }
    public function maquina()
    {
        return $this->belongsTo(InventariosMateriaPrima::class , 'plancha')->where('tipo', 'Plancha');
    }
    public function troquelado()
    {
        return $this->hasMany(CostoProduccion::class , 'ordentrabajo_id')->where('titulo', 'Troquelado');
    }
    public function terminado()
    {
        return $this->hasMany(CostoProduccion::class , 'ordentrabajo_id')->where('titulo', 'Terminado');
    }

    public function status()
    {
        return $this->hasMany(statusProduccion::class , 'idorden');
    }
    public function producto()
    {
        return $this->belongsTo(Articulo::class , 'articulo_id');
    }
    public function detalles()
    {
        return $this->hasMany(Detalletrabajo::class , 'ordentrabajo_id');
    }

    public function papel()
    {
        return $this->hasMany(CostoProduccion::class)->where('titulo', 'papel');
    }
    public function costos()
    {
        return $this->hasMany(CostoProduccion::class , 'ordentrabajo_id');
    }

    public function linea()
    {
        return $this->hasOne(LineaComprobante::class , 'ordentrabajo_id');
    }

    public function entregas()
    {
        return $this->hasMany(Entrega::class , 'ordentrabajo_id');
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($linea) {
            $linea->linea()->delete();
        });

        static::updated(function ($orden) {
            if ($orden->wasChanged('cantidad')) {
                $costoTerminado = CostoProduccion::where('ordentrabajo_id', $orden->id)
                    ->where('titulo', 'Terminado')
                    ->first();
                if ($costoTerminado) {
                    $costoTerminado->cantidad = $orden->cantidad;
                    $costoTerminado->total = $orden->cantidad * $costoTerminado->valor;
                    $costoTerminado->save();

                    $cuenta = CuentaPorPagar::where('costo_id', $costoTerminado->id)->first();
                    if ($cuenta) {
                        $cuenta->cantidad = $orden->cantidad;
                        $cuenta->monto = $costoTerminado->total;
                        
                        $totalAbonos = $cuenta->abonos()->sum('monto');
                        $cuenta->saldo = $cuenta->monto - $totalAbonos;
                        
                        $status = statusProduccion::where('idorden', $orden->id)->first();
                        $isTerminado = ($status && $status->estado === 'Terminado');

                        if ($cuenta->saldo <= 0) {
                            $cuenta->estado = 'Pagado';
                        } elseif ($totalAbonos > 0) {
                            $cuenta->estado = 'Abonado';
                        } else {
                            $cuenta->estado = $isTerminado ? 'En Espera' : 'Pendiente';
                        }
                        $cuenta->save();
                    }
                }
            }
        });
    }


}
