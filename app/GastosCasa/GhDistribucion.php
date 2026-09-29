<?php

namespace App\GastosCasa;

use Illuminate\Database\Eloquent\Model;

class GhDistribucion extends Model
{
    protected $table = 'gh_distribuciones';

    protected $fillable = [
        'ingreso_id',
        'tipo_destino',
        'persona_id',
        'gasto_id',
        'valor',
        'observaciones',
        'fecha'
    ];

    public function ingreso()
    {
        return $this->belongsTo(GhIngreso::class, 'ingreso_id');
    }

    public function persona()
    {
        return $this->belongsTo(GhPersona::class, 'persona_id');
    }

    public function gasto()
    {
        return $this->belongsTo(GhGasto::class, 'gasto_id');
    }

    public function reservas()
    {
        return $this->hasMany(GhReserva::class, 'distribucion_id');
    }
}
