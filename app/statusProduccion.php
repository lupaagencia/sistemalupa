<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class statusProduccion extends Model
{
    protected $table = 'statusproduccion';
    protected $fillable = [
        'id', 'idorden','estado','fecha_termina','hora','observaciones'
    ];
    public function ordentrabajo(){
        return $this->hasOne('App\Ordentrabajo');
    }
    public function orden(){
        return $this->belongsTo(Ordentrabajo::class,'idorden')->whereIn('produccion',['ENP','EP']);
    }
    public function procesos(){
        return $this->belongsTo(Procesos::class);
    }
   
    use HasFactory;
}
