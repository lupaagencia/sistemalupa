<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CostoProduccion extends Model
{
    protected $table = 'costos';
    protected $fillable =[
        'idorden','idcostois','titulo','descripcion','cantidad','valor','total','orden','completado','fecha_termina','Terminado'
    ];
    public function personas()
    {
        return $this->belongsTo('App\Ordentrabajo');
    }
    public function proveedores()
    {
        return $this->belongsTo('App\Costois');
    }
}
