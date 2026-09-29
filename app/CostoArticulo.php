<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CostoArticulo extends Model
{
    protected $table = 'costo_articulos';
    protected $fillable=['idarticulo', 'idcotois', 'titulo','descripcion','orden_produccion','fraccion','rentabilidad','cantidad','valor','valorfull'];
    public function opcion(){
        return $this->belongsTo('App\OpcionAtributo');
    }
    public function articulo(){
        return $this->belongsTo('App\Articulo');
    }
    public function costois(){
        return $this->belongsTo(Costois::class,'medida_final');

    }
    
}

