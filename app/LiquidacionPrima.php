<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiquidacionPrima extends Model
{
    use HasFactory;

    protected $table = 'liquidacion_primas';

    protected $fillable = [
        'empleado_id',
        'anio',
        'periodo',
        'fecha_pago',
        'dias_trabajados',
        'salario_base',
        'promedio_extras',
        'valor_prima',
        'egreso_id'
    ];

    protected $casts = [
        'dias_trabajados' => 'float',
        'salario_base' => 'float',
        'promedio_extras' => 'float',
        'valor_prima' => 'float'
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
