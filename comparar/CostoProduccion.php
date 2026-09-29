<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CostoProduccion extends Model
{
    protected $table = 'costos';
    protected $fillable =[
        'ordentrabajo_id','costois_id','titulo','descripcion','cantidad','valor','total','orden','completado','pago','fecha_termina','Terminado'
    ];
    public function personas()
    {
        return $this->belongsTo('App\Ordentrabajo');
    }
    public function proveedores()
    {
        return $this->belongsTo('App\Costois');
    }
    public function tipo()
    {
        return $this->belongsTo(TipoProducto::class);
    }
    public function costois(){
        return $this->belongsTo(Costois::class,'costois_id');

    }
}
