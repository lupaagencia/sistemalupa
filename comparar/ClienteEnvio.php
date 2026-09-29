<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteEnvio extends Model
{
    protected $table = 'cliente_envio';
    protected $fillable = [
        'cliente_id', 
        'datosenvio_id',
    ];
    public $timestamps = false;
}
