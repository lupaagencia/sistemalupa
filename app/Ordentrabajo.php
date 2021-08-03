<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ordentrabajo extends Model
{
    protected $table = 'ordentrabajos';
    protected $fillable = [
        'idcliente', 
        'idarticulo',
        'idcostois',
        'cantidad',
        'ancho_material',
        'largo_material',
        'impuesto',
        'produccion',
        'fecha',
        'unidad_medida',
        'detalles_diseno',
        'carpeta_cliente',
        'observaciones',
        'totalParcial',
        'descuento',
        'abono',
        'saldo',
        'total',
        'estado',
        'fecha_entrega'
    ];
    public function cliente()
     {
         return $this->belongsTo('App\Cliente');
     }
     public function articulo()
     {
         return $this->belongsTo('App\Articulo');
     }
     public function costo()
     {
         return $this->belongsTo('App\Costois');
     }
}
