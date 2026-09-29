<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class TipoProducto extends Model
{
    protected $table = 'tipo_producto';
    public $timestamps = false;
    protected $fillable = ['nombre', 'valor', 'area', 'tamano', 'impresiones', 'sobrante', 'cabida', 'piezas_por_pliego', 'tipo_cantidad', 'rangos', 'descripcion', 'imagen', 'orden', 'estado', 'gastos_fijos', 'rentabilidad', 'formula_ancho', 'formula_largo'];

    public function atributos()
    {
        return $this->hasMany(Atributo::class, 'id_articulo');
    }

    public function atributosTienda()
    {
        return $this->belongsToMany(AtributoTienda::class, 'tipo_producto_atributo_tienda', 'tipo_producto_id', 'atributo_tienda_id');
    }

    public function costos()
    {
        return $this->hasMany(CostoArticulo::class, 'idarticulo')->where('idopcion', '=', 0);
    }

    public function procesos()
    {
        return $this->hasMany(TipoProductoProceso::class, 'tipo_producto_id');
    }
}