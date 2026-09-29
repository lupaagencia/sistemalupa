<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DespieceProyecto extends Model
{
    protected $table = 'despiece_proyectos';

    protected $fillable = [
        'cliente',
        'descripcion',
        'fecha'
    ];

    public function muebles()
    {
        return $this->hasMany(DespieceMueble::class, 'proyecto_id');
    }
}
