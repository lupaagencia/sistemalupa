<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LiquidacionQuincena extends Model
{
    protected $table = 'liquidacion_quincena';

    protected $fillable = [
        'empleado_id',
        'fecha_pago',
        'fecha_inicio',
        'fecha_fin',
        'dias_trabajados',
        'salario_base',
        'sueldo_neto',
        'auxilio_transporte',
        'horas_extras_json',
        'monto_extras',
        'salud_deduccion',
        'pension_deduccion',
        'otras_deducciones',
        'neto_pagado',
        'egreso_id'
    ];

    protected $casts = [
        'dias_trabajados' => 'integer',
        'salario_base' => 'float',
        'sueldo_neto' => 'float',
        'auxilio_transporte' => 'float',
        'monto_extras' => 'float',
        'salud_deduccion' => 'float',
        'pension_deduccion' => 'float',
        'otras_deducciones' => 'float',
        'neto_pagado' => 'float'
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function egreso()
    {
        return $this->belongsTo(Egresos::class, 'egreso_id');
    }
}
