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
    ];
    public function inventario()
    {
        return $this->belongsTo(InventariosMateriaPrima::class);
    }
}
