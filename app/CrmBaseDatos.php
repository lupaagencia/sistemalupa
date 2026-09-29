<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CrmScopeTrait;

class CrmBaseDatos extends Model
{
    use CrmScopeTrait;

    protected $table = 'crm_bases_datos';

    protected $fillable = [
        'user_id',
        'nombre',
        'sector',
        'origen',
        'descripcion',
        'estado',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function contactos()
    {
        return $this->hasMany(CrmContactoBase::class, 'base_datos_id', 'id');
    }
}
