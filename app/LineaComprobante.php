<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineaComprobante extends Model
{
    protected $table = 'linea_comprobantes';
    protected $fillable = [
        'id', 'comprobante_id','articulo_id','ordentrabajo_id','fecha','fecha_entrega','cantidad','valor_unitario','subtotal','descuento','impuesto','valor_total','estado','orden'
    ];
    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
    public function orden()
    {
        return $this->belongsTo(Ordentrabajo::class,'ordentrabajo_id');
    }

    public function getOrdenAttribute($value)
    {
        if ($this->relationLoaded('orden') || array_key_exists('orden', $this->relations)) {
            return $this->getRelation('orden');
        }
        if (!empty($this->ordentrabajo_id)) {
            return $this->getRelationshipFromMethod('orden');
        }
        return $value;
    }
   
    public function pedido()
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
    }

    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
    }
  
}
