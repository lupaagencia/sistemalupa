<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    protected $table = 'turnos';

    protected $fillable = [
        'nombre',
        'hora_entrada',
        'tolerancia_minutos',
        'hora_salida_receso',
        'hora_entrada_receso',
        'hora_salida_almuerzo',
        'hora_entrada_almuerzo',
        'hora_salida',
        'estado'
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'turno_id');
    }

    public function dias()
    {
        return $this->hasMany(TurnoDia::class, 'turno_id')->orderBy('dia_num', 'asc');
    }

    public function obtenerConfigDia($diaNum)
    {
        $dia = $this->dias->where('dia_num', $diaNum)->first();
        if ($dia) return $dia;

        // Fallback si no existe configuración específica para ese día
        return (object)[
            'laborable' => !in_array($diaNum, [6, 7]), // Sábado y Domingo no laborables por defecto
            'hora_entrada' => $this->hora_entrada ?: '08:00:00',
            'tolerancia_minutos' => $this->tolerancia_minutos ?: 10,
            'hora_salida' => $this->hora_salida ?: '17:00:00',
            'hora_salida_receso' => $this->hora_salida_receso,
            'hora_entrada_receso' => $this->hora_entrada_receso,
            'hora_salida_almuerzo' => $this->hora_salida_almuerzo,
            'hora_entrada_almuerzo' => $this->hora_entrada_almuerzo,
        ];
    }
}
