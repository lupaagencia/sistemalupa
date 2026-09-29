<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Datosenvio extends Model
{
    protected $table = 'datosenvio';
    protected $fillable = [
        'id', 'idcliente','contacto','empresa','tipo_documento','documento','telefono','direccion','ciudad','pais'
    ];
    public $timestamps = false;
    public function clientes(){
        return $this->belongsToMany(Cliente::class, 'cliente_envio');
    }
    
   
}
