<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procesos extends Model
{
    protected $table = 'procesos';
    protected $fillable = [
        'id', 'posicion','proceso','cantidad','fecha_termina','hora'
    ];
   
    use HasFactory;
}
