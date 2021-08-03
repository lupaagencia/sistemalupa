<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    protected $fillable = [
        'idcliente', 
        'idarticulo',
        'idcostois',
        'cantidad',
        'ancho_material',
        'largo_material',
        'areas_impresas',
        'tipo_impresion',
        'colores_impresion',
        'unidad_medida',
        'detalles_diseno',
        'carpeta_cliente',
        'tamano',
        'abono',
        'saldo',
        'total',
        'estado',
        'fecha_entrega',

     ];
     public function usuario()
     {
         return $this->belongsTo('App\User');
     }
     public function proveedor()
     {
         return $this->belongsTo('App\Proveedor');
     }
}
