<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Imagenes extends Model
{
    protected $fillable =['id_tabla','nombre','orden'];
    public $timestamps = false;
    public function imagenes(){
        return $this->hasMany('App\Imagenes');
    }
}