<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OpcionAtributo extends Model
{
    protected $table = 'opcion_atributos';
    protected $fillable =['id_atributo','label','valor','opciones','descripcion','alerta','orden'];
    public $timestamps = false;
    public function atributo(){
        return $this->belongsTo('App\Atributo');
    }
}
