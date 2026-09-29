<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineaComprobante extends Model
{
    protected $table = 'linea_comprobantes';
    protected $fillable = [
        'id', 'comprobante_id','articulo_id','ordentrabajo_id','fecha','fecha_entrega','cantidad','valor_unitario','subtotal','descuento','impuesto','valor_total','estado'
    ];
    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
    public function orden()
    {
        return $this->belongsTo(Ordentrabajo::class,'ordentrabajo_id');
    }
   
    public function pedido()
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
           
    }
  
}
