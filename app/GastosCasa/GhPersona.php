<?php

namespace App\GastosCasa;

use Illuminate\Database\Eloquent\Model;

class GhPersona extends Model
{
    protected $table = 'gh_personas';

    protected $fillable = [
        'nombre',
        'user_id',
        'telefono',
        'email',
        'porcentaje_participacion',
        'activo'
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function gastos()
    {
        return $this->hasMany(GhGasto::class, 'persona_id');
    }

    public function distribuciones()
    {
        return $this->hasMany(GhDistribucion::class, 'persona_id');
    }
}
