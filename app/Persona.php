<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $fillable = ['id', 'nombre', 'tipo_documento', 'num_documento', 'direccion', 'telefono', 'email'];

    public $timestamps = true;

    public function provedor()
    {
        return $this->hasOne('App\Proveedor', 'id', 'id');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'id');
    }

    public function cliente()
    {
        return $this->hasOne('App\Cliente', 'id', 'id');
    }
}
