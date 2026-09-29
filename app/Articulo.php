<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Articulo extends Model
{
    protected $fillable = ['id_item_padre', 'idcategoria', 'codigo', 'tipo_producto_id', 'tipo_cantidad', 'nombre', 'imagen', 'rangos', 'precio_venta', 'iva', 'stock', 'tamano', 'medida_final', 'ancho_final', 'largo_final', 'descripcion', 'condicion', 'cabidas_materiales'];

    protected $casts = [
        'cabidas_materiales' => 'array'
    ];
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'idcategoria');
    }
    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'articulo_categoria', 'articulo_id', 'categoria_id');
    }
    public function subarticulo()
    {
        return $this->belongsTo(Articulo::class, 'id_item_padre');
    }
    public function subproductos()
    {
        return $this->hasMany(Articulo::class, 'id_item_padre');
    }

    public function tipo()
    {
        return $this->belongsTo(TipoProducto::class, 'tipo_producto_id');
    }
    public function imagenes()
    {
        return $this->hasMany(Imagenes::class, 'id_tabla');
    }

    public function ordentrabajo()
    {
        return $this->hasMany('App\Ordentrabajo');
    }
    public function costos()
    {
        return $this->hasMany(CostoArticulo::class);
    }
    public function troqueles()
    {
        return $this->hasMany(ArticuloTroquel::class, 'articulo_id');
    }
}