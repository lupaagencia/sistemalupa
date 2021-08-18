<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Articulo extends Model
{
    protected $fillable =['id_item_padre','idcategoria','codigo','nombre','imagen','rangos','precio_venta','iva','stock','descripcion','condicion'];
    public function categoria(){
        return $this->belongsTo('App\Categoria');
    }
    public function articulo(){
        return $this->belongsTo('App\Articulo');
    }
    public function atributo(){
        return $this->hasMany('App\Atributo');
    }
    public function ordentrabajo(){
        return $this->hasOne('App\Ordentrabajo');
    }
}