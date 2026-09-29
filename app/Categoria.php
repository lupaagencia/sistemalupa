<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    //protected $table='categorias';
    protected $fillable=['nombre', 'descripcion', 'condicion', 'imagen', 'padre_id', 'banner'];
    
    public function articulos(){
        return $this->belongsToMany('App\Articulo', 'articulo_categoria', 'categoria_id', 'articulo_id');
    }

    public function padre() {
        return $this->belongsTo('App\Categoria', 'padre_id');
    }

    public function sublevels() {
        return $this->hasMany('App\Categoria', 'padre_id')->with('sublevels');
    }
}
