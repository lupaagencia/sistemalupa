<?php

namespace App\GastosCasa;

use Illuminate\Database\Eloquent\Model;

class GhReserva extends Model
{
    protected $table = 'gh_reservas';

    protected $fillable = [
        'tipo_reserva',
        'tipo_movimiento',
        'distribucion_id',
        'fecha',
        'concepto',
        'valor',
        'soporte_path'
    ];

    public function distribucion()
    {
        return $this->belongsTo(GhDistribucion::class, 'distribucion_id');
    }
}
