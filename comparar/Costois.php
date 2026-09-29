<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Costois extends Model
{
    protected $fillable =[
        'idproveedor','idpersona','tipo_costo','nombre','descripcion','unidad_medida','valor','total','estado'
    ];
    public function costos(){
        return $this->hasMany('App/CostoProduccion');
    }
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'idpersona');
    }
    public function proveedores()
    {
        return $this->belongsTo('App\Proveedor');
    }
    public function ordentrabajo(){
        return $this->hasOne('App\Ordentrabajo');
    }
}

