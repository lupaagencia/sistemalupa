<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineaComprobante extends Model
{
    protected $table = 'linea_comprobantes';
    protected $fillable = [
        'id', 'id_comprobante','id_articulo','fecha','fecha_entrega','cantidad','valor_unitario','descuento','valor_total','estado'
    ];
  
}
