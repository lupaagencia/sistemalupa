<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $fillable = [
        'id', 'tipo_cliente','ciudad','departamento','pais','contacto','telefono_contacto','email_contacto'
    ];
    public $timestamps = false;
    public function persona()
    {
        return $this->belongsTo('App\Persona');
    } 
    public function ordentrabajo(){
        return $this->hasOne('App\Ordentrabajo');
    }  //
}
