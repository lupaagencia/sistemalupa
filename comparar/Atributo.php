<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Atributo extends Model
{
    protected $fillable =[
        'id_articulo',
        'valor',
        'tipo_atributo',
        'tipo_campo',
        'tipo_valor',
        'nombre',
        'tipo_impresion',
        'nota',
        'descripcion',
        'alerta',
        'cabida',
        'operacion',
        'minimo',
        'maximo',
        'orden'
    ];
    public function opciones(){
        return $this->hasMany(OpcionAtributo::class,'id_atributo');
    }
}

