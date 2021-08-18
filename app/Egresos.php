<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Egresos extends Model
{
  
    protected $fillable = [
        'id_documento',
        'id_persona',
        'valor',
        'cuenta_contable',
        'tipo_documento',
        'tipo_egreso',
        'total',
        'estado',
        'fecha',
        ];
        public function personas()
        {
            return $this->belongsTo('App\Persona');
        }


}
