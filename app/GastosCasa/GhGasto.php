<?php

namespace App\GastosCasa;

use Illuminate\Database\Eloquent\Model;

class GhGasto extends Model
{
    protected $table = 'gh_gastos';

    protected $fillable = [
        'persona_id',
        'origen_pago',
        'categoria_id',
        'fecha',
        'descripcion',
        'valor',
        'soporte_path',
        'soporte_nombre_orig',
        'comprobante_path',
        'comprobante_nombre_orig',
        'estado',
        'saldo_pendiente',
        'notas'
    ];

    public function persona()
    {
        return $this->belongsTo(GhPersona::class, 'persona_id');
    }

    public function categoria()
    {
        return $this->belongsTo(GhCategoria::class, 'categoria_id');
    }

    public function distribuciones()
    {
        return $this->hasMany(GhDistribucion::class, 'gasto_id');
    }
}
