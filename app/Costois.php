<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Costois extends Model
{
    protected $fillable =[
        'idproveedor','idpersona','tipo_costo','nombre','unidad_medida','valor','total','estado'
    ];
    public function personas()
    {
        return $this->belongsTo('App\Persona');
    }
    public function proveedores()
    {
        return $this->belongsTo('App\Proveedor');
    }
    public function ordentrabajo(){
        return $this->hasOne('App\Ordentrabajo');
    }
}
