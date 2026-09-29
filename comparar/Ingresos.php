<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ingresos extends Model
{
    protected $fillable = [
        'idcliente',
        'idusuario',
        'tipo_documento',
        'serie_comprobante',
        'num_comprobante',
        'fecha_hora',
        'impuestos',
        'forma_pago',
        'subtotal',
        'iva',
        'total',
        'estado',
        'fecha',
        ];
    public function cliente()
    {
        return $this->belongsTo('App\Cliente', 'idcliente');
    }
        
}
