<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reportes extends Model
{
    protected $fillable = ['iduser','tipo','fecha','archivo','estado'];

  
}
