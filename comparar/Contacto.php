<?php

namespace App   ;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    protected $fillable = [
        'id', 'nombre','telefono','telefono_particular','correo','tipo_contacto','cargo','nombre_asistente','telefono_asistente','fecha_nacimiento'
    ];
    use HasFactory;
    public function clientes(){
        return $this->belongsToMany(Cliente::class, 'cliente_contacto');
    }
    
}
