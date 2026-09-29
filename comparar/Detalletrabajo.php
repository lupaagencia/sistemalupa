<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Detalletrabajo extends Model
{
    protected $table = 'detalletrabajos';
    protected $fillable=['ordentrabajo_id','costos_id', 'titulo','descripcion','valor'];
    public function ordentrabajo()
    {
        return $this->belongsTo(Ordentrabajo::class);
    }
    public function costo(){
        return $this->belongsTo(CostoProduccion::class,'costos_id');
    }
}

