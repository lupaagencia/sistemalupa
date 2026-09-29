<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrmGestionContactoLog extends Model
{
    protected $table = 'crm_gestion_contactos_log';

    protected $fillable = [
        'contacto_id',
        'user_id',
        'tipo_accion',
        'detalle',
    ];

    public function contacto()
    {
        return $this->belongsTo(CrmContactoBase::class, 'contacto_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
