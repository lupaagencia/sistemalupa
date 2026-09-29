<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrmMetaVenta extends Model
{
    protected $table = 'crm_metas_ventas';

    protected $fillable = [
        'user_id',
        'mes',
        'anio',
        'monto_meta'
    ];

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
