<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class comprobate extends Model
{
    protected $fillable = [
        'id', 'num_comprobante','id_cliente','id_user','fecha','forma_pago','subtotal','descuento', 'total','impuestos','fuente','estado'
    ];
}
