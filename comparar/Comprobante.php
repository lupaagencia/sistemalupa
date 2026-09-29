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
        'monto_aplicado_anticipo'
    ];
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
        return $this->hasMany(LineaComprobante::class, 'comprobante_id');
    }

    /**
     * Retorna el ID del pedido original al que pertenece este comprobante.
     */
    public function getPedidoPadreIds($visited = [])
    {
        if (in_array($this->id, $visited))
            return [];
        $visited[] = $this->id;

        if ($this->tipo === 'pedido') {
            return [$this->id];
        }

        // 1. Intentar por la tabla puente pedidos_remision
        $ids = \App\PedidoRemision::where('remision_id', $this->id)
            ->orWhere('cuentacobro_id', $this->id)
            ->pluck('pedido_id')
            ->unique()
            ->toArray();

        if (!empty($ids)) {
            return $ids;
        }

        // 2. Fallback: fuente_id (csv)
        if ($this->fuente_id) {
            $fids = explode(',', $this->fuente_id);
            foreach ($fids as $fid) {
                $fid = trim($fid);
                if (!is_numeric($fid))
                    continue;
                $fuente = self::find($fid);
                if ($fuente) {
                    if ($fuente->tipo === 'pedido') {
                        $ids[] = $fuente->id;
                    } else {
                        $pids = $fuente->getPedidoPadreIds($visited);
                        if ($pids)
                            $ids = array_merge($ids, $pids);
                    }
                }
            }
        }

        if (empty($ids) && $this->pedido_id) {
            $ids[] = $this->pedido_id;
        }

        // 3. Fallback: buscar por ordentrabajo_id en lineas (relacion historica)
        if (empty($ids)) {
            $line = $this->lineas()->whereNotNull('ordentrabajo_id')->first();
            if ($line) {
                $pedIds = LineaComprobante::where('ordentrabajo_id', $line->ordentrabajo_id)
                    ->whereHas('pedido', function ($q) {
                        $q->where('tipo', 'pedido');
                    })
                    ->pluck('comprobante_id')->toArray();
                $ids = array_merge($ids, $pedIds);
            }
        }

        return array_unique($ids);
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

        static::deleting(function ($linea) {
            $linea->lineas()->delete();
        });
    }
}
