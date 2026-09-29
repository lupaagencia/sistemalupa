<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CrmScopeTrait;

class CrmContactoBase extends Model
{
    use CrmScopeTrait;

    protected $table = 'crm_contactos_base';

    protected $fillable = [
        'base_datos_id',
        'user_id',
        'empresa',
        'contacto_nombre',
        'cargo',
        'sector',
        'telefono',
        'email',
        'ciudad',
        'direccion',
        'origen_detalle',
        'estado_gestion',
        'portafolio_enviado',
        'fecha_envio_portafolio',
        'metodo_envio',
        'resultado_gestion',
        'fecha_ultimo_contacto',
        'prospecto_id',
    ];

    protected $casts = [
        'portafolio_enviado' => 'boolean',
        'fecha_envio_portafolio' => 'datetime',
        'fecha_ultimo_contacto' => 'datetime',
    ];

    public function baseDatos()
    {
        return $this->belongsTo(CrmBaseDatos::class, 'base_datos_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function prospecto()
    {
        return $this->belongsTo(CrmProspecto::class, 'prospecto_id', 'id');
    }

    public function logs()
    {
        return $this->hasMany(CrmGestionContactoLog::class, 'contacto_id', 'id')->orderBy('id', 'desc');
    }
}
