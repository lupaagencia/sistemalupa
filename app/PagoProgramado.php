<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PagoProgramado extends Model
{
    protected $table = 'pagos_programados';
    protected $fillable = [
        'concepto',
        'categoria',
        'proveedor_id',
        'beneficiario',
        'monto_estimado',
        'frecuencia',
        'proxima_fecha_pago',
        'recordatorio_dias',
        'cuenta_id',
        'estado',
        'observaciones'
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }
}
