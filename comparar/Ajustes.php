<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ajustes extends Model
{
    //protected $table='categorias';
    protected $fillable = ['tipo', 'detalle', 'valor', 'categoria'];
    public $timestamps = false;


}
