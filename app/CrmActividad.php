<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CrmScopeTrait;

class CrmActividad extends Model
{
    use CrmScopeTrait;
    protected $table = 'crm_actividades';

    protected $fillable = [
        'prospecto_id',
        'oportunidad_id',
        'cotizacion_id',
        'user_id',
        'tipo',
        'asunto',
        'descripcion',
        'fecha_vencimiento',
        'completada',
        'fecha_completada'
    ];

    public function prospecto()
    {
        return $this->belongsTo(CrmProspecto::class, 'prospecto_id');
    }

    public function oportunidad()
    {
        return $this->belongsTo(CrmOportunidad::class, 'oportunidad_id');
    }

    public function cotizacion()
    {
        return $this->belongsTo(CrmCotizacion::class, 'cotizacion_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
