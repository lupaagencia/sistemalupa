<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DespieceMueble extends Model
{
    protected $table = 'despiece_muebles';

    protected $fillable = [
        'proyecto_id',
        'nombre',
        'tipo_mueble',
        'ancho',
        'alto',
        'profundidad',
        'espesor_material',
        'material',
        'material_interno',
        'material_externo',
        'tipo_meson',
        'costados_vistos',
        'sistema_apertura',
        'tipo_tirador',
        'alto_meson',
        'tiene_fondo',
        'material_fondo',
        'ancho_derecho',
        'hueco_alto',
        'hueco_ancho',
        'espacio_ciego',
        'notas'
    ];

    public function proyecto()
    {
        return $this->belongsTo(DespieceProyecto::class, 'proyecto_id');
    }

    public function piezas()
    {
        return $this->hasMany(DespiecePieza::class, 'mueble_id');
    }

    public function divisiones()
    {
        return $this->hasMany(DespieceDivision::class, 'mueble_id')->orderBy('posicion', 'asc');
    }
}
