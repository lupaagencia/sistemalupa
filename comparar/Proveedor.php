<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{ 
    protected $table = 'proveedores';
    protected $fillable = [
        'id', 'nombre', 'contacto', 'telefono_contacto', 'tipo_documento', 'num_documento', 'direccion', 'telefono', 'email', 'cupo_credito'
    ];

    public $timestamps = true;

   
}
