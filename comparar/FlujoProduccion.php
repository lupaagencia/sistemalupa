<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

use App\Ordentrabajo;

class FlujoProduccion extends Model
{
    protected $table = 'flujo_produccion';

    protected $fillable = [
        'orden_trabajo_id',
        'proceso',
        'fecha_inicia',
        'fecha_termina',
        'hora_inicia',
        'hora_termina',
        'cantidad',
        'siguiente_proceso',
        'usuario',
        'observaciones'
    ];

    protected $casts = [
        'fecha_inicia' => 'date',
        'fecha_termina' => 'date',
        'hora_inicia' => 'string',
        'hora_termina' => 'string',
        'cantidad' => 'integer'
    ];
    public function platform_user()
    {
        return $this->belongsTo(User::class, 'usuario');
    }

    public function operario()
    {
        return $this->belongsTo(User::class, 'usuario');
    }

    public function orden_trabajo_id()
    {
        return $this->belongsTo(Ordentrabajo::class, 'orden_trabajo_id');
    }

    public function ordenTrabajo()
    {
        return $this->belongsTo(Ordentrabajo::class, 'orden_trabajo_id');
    }
}