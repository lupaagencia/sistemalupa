<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Detalletrabajo extends Model
{
    protected $fillable=['idorden', 'titulo','descripcion','valor','orden'];

    public function ordentrabajo()
    {
        return $this->belongsTo(Ordentrabajo::class);
    }
    public function costo(){
        return $this->belongsTo(CostoProduccion::class,'costos_id');
    }
}
