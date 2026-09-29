<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClasificacionEgreso extends Model
{
    protected $table = 'clasificaciones_egresos';

    protected $fillable = [
        'nombre',
        'descripcion'
    ];
}
