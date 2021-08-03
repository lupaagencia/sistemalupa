<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Costo extends Model
{
    protected $fillable=['idorden', 'idcotois', 'titulo','descripcion','cantidad','valor','total'];
   
}
