<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $table = 'asistencias';

    protected $fillable = [
        'empleado_id',
        'turno_id',
        'fecha',
        'tipo_evento',
        'hora_marcada',
        'estado_llegada',
        'minutos_tardanza',
        'minutos_extras',
        'latitud',
        'longitud',
        'distancia_metros',
        'observaciones'
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class, 'turno_id');
    }
}
