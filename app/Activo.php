<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activo extends Model
{
    //protected $table='categorias';
    protected $fillable=['tipo', 'activo', 'descripcion','ubicacion','responsable','clasificacion','estado','grupo','datos_activo'];
    public function cliente(){
        return $this->hasMany(Cliente::class);
    }
}
