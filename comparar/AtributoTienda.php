<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AtributoTienda extends Model
{
    protected $table = 'atributos_tienda';
    protected $fillable = ['tipo', 'nombre', 'etiquetas', 'imagen', 'valor_extra', 'formula', 'condicion', 'dependencia', 'es_buscable', 'seleccion_multiple', 'mostrar_en_producto', 'activo'];

    public function tiposProducto()
    {
        return $this->belongsToMany(TipoProducto::class, 'tipo_producto_atributo_tienda', 'atributo_tienda_id', 'tipo_producto_id');
    }
}
