<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroProduccion extends Model
{
    use HasFactory;
      protected $table = 'registros_produccion';

    protected $fillable = [
        'orden_trabajo_id',
        'empleado_id',
        'actividad',
        'elemento',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'minutos',
        'cantidad',
        'unidad',
        'observaciones'
    ];

    // Relaciones
    public function ordenTrabajo()
    {
        return $this->belongsTo(Ordentrabajo::class, 'orden_trabajo_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
