<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Detalletrabajo extends Model
{
    protected $fillable=['idorden', 'titulo','descripcion','valor'];
}
