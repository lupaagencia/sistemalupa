<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrmEtapa extends Model
{
    protected $table = 'crm_etapas';

    protected $fillable = [
        'nombre',
        'orden',
        'probabilidad',
        'color',
        'activo'
    ];

    public function oportunidades()
    {
        return $this->hasMany(CrmOportunidad::class, 'etapa_id');
    }
}
