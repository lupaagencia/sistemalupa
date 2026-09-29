<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DespiecePieza extends Model
{
    protected $table = 'despiece_piezas';

    protected $fillable = [
        'mueble_id',
        'nombre_pieza',
        'material',
        'cantidad',
        'largo',
        'ancho',
        'canto_l1',
        'canto_l2',
        'canto_a1',
        'canto_a2'
    ];

    protected $casts = [
        'canto_l1' => 'boolean',
        'canto_l2' => 'boolean',
        'canto_a1' => 'boolean',
        'canto_a2' => 'boolean',
        'largo' => 'float',
        'ancho' => 'float',
    ];

    public function mueble()
    {
        return $this->belongsTo(DespieceMueble::class, 'mueble_id');
    }
}
