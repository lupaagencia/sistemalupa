<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CrmScopeTrait;

class CrmProspecto extends Model
{
    use CrmScopeTrait;
    protected $table = 'crm_prospectos';

    protected $fillable = [
        'nombre',
        'empresa',
        'cargo',
        'email',
        'telefono',
        'celular',
        'direccion',
        'ciudad',
        'origen',
        'estado',
        'user_id',
        'cliente_id',
        'observaciones'
    ];

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Persona::class, 'cliente_id');
    }

    public function oportunidades()
    {
        return $this->hasMany(CrmOportunidad::class, 'prospecto_id');
    }

    public function cotizaciones()
    {
        return $this->hasMany(CrmCotizacion::class, 'prospecto_id');
    }

    public function actividades()
    {
        return $this->hasMany(CrmActividad::class, 'prospecto_id');
    }
}
