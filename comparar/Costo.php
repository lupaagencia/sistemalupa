<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Costo extends Model
{
    protected $fillable=['ordentrabajo_id', 'costois_id', 'titulo','descripcion','cantidad','valor','total'];
    public function costois(){
        return $this->hasMany(Costois::class);
    }
   
}
