<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    protected $table = 'comprobantes';
    protected $fillable = [
        'id',
        'tipo',
        'num_comprobante',
        'fuente_id',
        'cliente_id',
        'datos_factura_id',
        'user_id',
        'fecha',
        'forma_pago',
        'subtotal',
        'descuento',
        'total',
        'impuestos',
        'abono',
        'saldo',
        'estado',
        'transportadora',
        'pedido_id',
        'monto_aplicado_anticipo',
        'comprobante_contable_id'
    ];

    protected $appends = ['num_remisiones'];

    /**
     * Retorna la cantidad de remisiones asociadas a este pedido.
     */
    public function getNumRemisionesAttribute()
    {
        if ($this->tipo !== 'pedido') {
            return 0;
        }

        try {
            // 1. Por la tabla puente pedidos_remision
            $r1 = \App\PedidoRemision::where('pedido_id', $this->id)
                ->whereNotNull('remision_id')
                ->pluck('remision_id')
                ->toArray();

            // 2. Por fuente_id o pedido_id en comprobantes tipo remisión
            $r2 = self::where('tipo', 'remision')
                ->where(function($q) {
                    $q->where('pedido_id', $this->id)
                      ->orWhere('fuente_id', 'LIKE', '%'.$this->id.'%');
                })
                ->pluck('id')
                ->toArray();

            // 3. Por ordentrabajo_id compartidos en las líneas
            $otIds = \App\LineaComprobante::where('comprobante_id', $this->id)->pluck('ordentrabajo_id')->filter()->toArray();
            $r3 = [];
            if (!empty($otIds)) {
                $r3 = \App\LineaComprobante::whereIn('ordentrabajo_id', $otIds)
                    ->whereHas('pedido', function($q) {
                        $q->where('tipo', 'remision');
                    })
                    ->pluck('comprobante_id')
                    ->toArray();
            }

            $remisionIds = array_unique(array_filter(array_merge($r1, $r2, $r3)));
            return count($remisionIds);
        } catch (\Exception $e) {
            return 0;
        }
    }
    public function comprobanteContable()
    {
        return $this->belongsTo(ComprobanteContable::class, 'comprobante_contable_id');
    }
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    public function pedido()
    {
        return $this->belongsTo(Comprobante::class, 'pedido_id');
    }
    public function cruces()
    {
        return $this->hasMany(CruceCartera::class, 'comprobante_id');
    }
    public function razonsocial()
    {
        return $this->belongsTo(Facturacion::class, 'datos_factura_id');
    }
    public function usuario()
    {
        return $this->belongsTo('App\User');
    }
    public function lineas()
    {
        return $this->hasMany(LineaComprobante::class, 'comprobante_id')->orderBy('orden', 'asc')->orderBy('id', 'asc');
    }
    public function facturaElectronica()
    {
        return $this->hasOne(FacturaElectronica::class, 'comprobante_id');
    }


    /**
     * Retorna el ID del pedido original al que pertenece este comprobante.
     */
    public function getPedidoPadreIds($visited = [])
    {
        if (in_array($this->id, $visited)) {
            return [];
        }
        $visited[] = $this->id;

        if ($this->tipo === 'pedido') {
            return [$this->id];
        }

        $ids = [];

        // 1. Intentar por la tabla puente pedidos_remision
        $remIds = \App\PedidoRemision::where('cuentacobro_id', $this->id)
            ->orWhere('remision_id', $this->id)
            ->pluck('remision_id')
            ->filter()
            ->toArray();

        $pids1 = \App\PedidoRemision::where('cuentacobro_id', $this->id)
            ->orWhere('remision_id', $this->id)
            ->pluck('pedido_id')
            ->filter()
            ->toArray();

        if (!empty($remIds)) {
            $pids2 = \App\PedidoRemision::whereIn('remision_id', $remIds)
                ->pluck('pedido_id')
                ->filter()
                ->toArray();
            $ids = array_merge($ids, $pids1, $pids2);
        } else {
            $ids = array_merge($ids, $pids1);
        }

        // 2. fuente_id (csv)
        if ($this->fuente_id) {
            $fids = explode(',', $this->fuente_id);
            foreach ($fids as $fid) {
                $fid = trim($fid);
                if (!is_numeric($fid)) continue;
                $fuente = self::find($fid);
                if ($fuente) {
                    if ($fuente->tipo === 'pedido') {
                        $ids[] = $fuente->id;
                    } else {
                        $pids = $fuente->getPedidoPadreIds($visited);
                        if ($pids) {
                            $ids = array_merge($ids, $pids);
                        }
                    }
                }
            }
        }

        // 3. pedido_id directo
        if ($this->pedido_id) {
            $ids[] = $this->pedido_id;
        }

        // 4. Buscar por ordentrabajo_id en TODAS las líneas del comprobante o de sus remisiones fuente
        $otIds = $this->lineas()->pluck('ordentrabajo_id')->filter()->toArray();

        if (empty($otIds) && $this->fuente_id) {
            $fids = array_filter(array_map('trim', explode(',', $this->fuente_id)));
            if (!empty($fids)) {
                $otIds = LineaComprobante::whereIn('comprobante_id', $fids)
                    ->pluck('ordentrabajo_id')
                    ->filter()
                    ->toArray();
            }
        }

        if (!empty($otIds)) {
            $pedIds = LineaComprobante::whereIn('ordentrabajo_id', array_unique($otIds))
                ->whereHas('pedido', function ($q) {
                    $q->where('tipo', 'pedido');
                })
                ->pluck('comprobante_id')
                ->toArray();
            $ids = array_merge($ids, $pedIds);
        }

        return array_values(array_unique(array_filter($ids)));
    }

    public function getPedidoPadreId($visited = [])
    {
        if (in_array($this->id, $visited))
            return null;
        $visited[] = $this->id;

        if ($this->tipo === 'pedido') {
            return $this->id;
        }

        // 1. Intentar por la tabla puente
        $id = \App\PedidoRemision::where('remision_id', $this->id)
            ->orWhere('cuentacobro_id', $this->id)
            ->value('pedido_id');

        if ($id)
            return (int) $id;

        if ($this->pedido_id) {
            return $this->pedido_id;
        }

        if ($this->fuente_id) {
            $ids = explode(',', $this->fuente_id);
            $firstId = trim(reset($ids));
            if (is_numeric($firstId)) {
                $fuente = self::find($firstId);
                if ($fuente) {
                    if ($fuente->tipo === 'pedido') {
                        return $fuente->id;
                    }
                    // Recursivo si es una remisión u otro tipo intermedio
                    return $fuente->getPedidoPadreId($visited);
                }
            }
        }

        // Fallback: buscar por ordentrabajo_id compartido en lineas
        $line = $this->lineas()->whereNotNull('ordentrabajo_id')->first();
        if ($line) {
            $pedId = LineaComprobante::where('ordentrabajo_id', $line->ordentrabajo_id)
                ->whereHas('pedido', function ($q) {
                    $q->where('tipo', 'pedido');
                })
                ->value('comprobante_id');
            return $pedId;
        }

        return null;
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($comprobante) {
            $comprobante->lineas()->delete();
            if ($comprobante->comprobante_contable_id) {
                $compC = \App\ComprobanteContable::find($comprobante->comprobante_contable_id);
                if ($compC) {
                    $compC->delete();
                }
            }
        });
    }
}
