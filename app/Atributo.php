<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Atributo extends Model
{
    protected $fillable =[
        'id_articulo',
        'valor',
        'tipo_campo',
        'tipo_valor',
        'nombre',
        'tipo_impresion',
        'nota',
        'descripcion',
        'alerta',
        'unidad_medida',
        'operacion',
        'minimo',
        'maximo',
        'orden'
    ];
    public $timestamps = false;
    public function opcionAtributo(){
        return $this->hasMany('App/OpcionAtributo');
    }
}
