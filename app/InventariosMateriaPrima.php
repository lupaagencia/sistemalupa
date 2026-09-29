<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventariosMateriaPrima extends Model
{
    //protected $table='categorias';
    protected $fillable=['asignado_id','referencia', 'tipo', 'ubicacion','costois_id','detalles','estado','uso','cantidad','unidad','cambio','fecha_reemplazo'];
   
    public function cliente(){
        return $this->belongsTo(Cliente::class,'asignado_id');
    }
    public function costois(){
        return $this->belongsTo(Costois::class);
    }
}
