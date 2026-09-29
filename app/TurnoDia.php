<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurnoDia extends Model
{
    use HasFactory;

    protected $table = 'turno_dias';

    protected $fillable = [
        'turno_id',
        'dia_num',
        'dia_nombre',
        'laborable',
        'hora_entrada',
        'tolerancia_minutos',
        'hora_salida_receso',
        'hora_entrada_receso',
        'hora_salida_almuerzo',
        'hora_entrada_almuerzo',
        'hora_salida'
    ];

    protected $casts = [
        'laborable' => 'boolean',
        'dia_num' => 'integer',
        'tolerancia_minutos' => 'integer'
    ];

    public function turno()
    {
        return $this->belongsTo(Turno::class, 'turno_id');
    }
}
