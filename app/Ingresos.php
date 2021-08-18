<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ingresos extends Model
{
    protected $fillable = [
        'id_documento',
        'id_persona',
        'cuenta_contable',
        'tipo_documento',
        'tipo_ingreso',
        'forma_pago',
        'subtotal',
        'iva',
        'total',
        'estado',
        'fecha',
        ];
        public function personas()
    {
        return $this->belongsTo('App\Persona');
    }
        
}
