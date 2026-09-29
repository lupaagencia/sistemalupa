<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoProductoProceso extends Model
{
    protected $table = 'tipo_producto_procesos';
    protected $fillable = ['tipo_producto_id', 'nombre', 'costo_unitario'];

    public function tipoProducto()
    {
        return $this->belongsTo(TipoProducto::class, 'tipo_producto_id');
    }
}
