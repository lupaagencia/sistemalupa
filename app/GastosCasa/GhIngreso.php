<?php

namespace App\GastosCasa;

use Illuminate\Database\Eloquent\Model;

class GhIngreso extends Model
{
    protected $table = 'gh_ingresos';

    protected $fillable = [
        'tipo_ingreso',
        'fecha',
        'periodo_mes',
        'inquilino_nombre',
        'descripcion',
        'valor_total',
        'comprobante_path',
        'notas'
    ];

    public function distribuciones()
    {
        return $this->hasMany(GhDistribucion::class, 'ingreso_id');
    }
}
