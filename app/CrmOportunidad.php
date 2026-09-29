<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CrmScopeTrait;

class CrmOportunidad extends Model
{
    use CrmScopeTrait;
    protected $table = 'crm_oportunidades';

    protected $fillable = [
        'codigo',
        'nombre',
        'prospecto_id',
        'cliente_id',
        'user_id',
        'etapa_id',
        'monto_estimado',
        'probabilidad',
        'fecha_cierre_estimada',
        'estado',
        'motivo_perdida',
        'observaciones'
    ];

    public function prospecto()
    {
        return $this->belongsTo(CrmProspecto::class, 'prospecto_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Persona::class, 'cliente_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function etapa()
    {
        return $this->belongsTo(CrmEtapa::class, 'etapa_id');
    }

    public function cotizaciones()
    {
        return $this->hasMany(CrmCotizacion::class, 'oportunidad_id');
    }

    public function actividades()
    {
        return $this->hasMany(CrmActividad::class, 'oportunidad_id');
    }
}
