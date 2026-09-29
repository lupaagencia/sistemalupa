<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DespieceDivision extends Model
{
    protected $table = 'despiece_divisiones';

    protected $fillable = [
        'mueble_id',
        'posicion',
        'tipo',
        'cantidad'
    ];

    public function mueble()
    {
        return $this->belongsTo(DespieceMueble::class, 'mueble_id');
    }
}
