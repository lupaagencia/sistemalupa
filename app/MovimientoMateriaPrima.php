<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoMateriaPrima extends Model
{
    use HasFactory;
    protected $fillable = [
        'inventarios_materia_prima_id',
        'proveedores_id',
        'tipo',
        'cantidad',
        'costo_unitario',
        'costo_total',
        'comprobante_id',
    ];
    public function inventario()
    {
        return $this->belongsTo(InventariosMateriaPrima::class, 'inventarios_materia_prima_id');
    }
    public function comprobante()
    {
        return $this->belongsTo(ComprobanteContable::class, 'comprobante_id');
    }
}
