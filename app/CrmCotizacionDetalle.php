<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrmCotizacionDetalle extends Model
{
    protected $table = 'crm_cotizacion_detalles';

    protected $fillable = [
        'cotizacion_id',
        'articulo_id',
        'concepto',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'descuento_porcentaje',
        'subtotal',
        'iva',
        'total',
        'cotizador_tipo',
        'cotizador_data'
    ];

    public function cotizacion()
    {
        return $this->belongsTo(CrmCotizacion::class, 'cotizacion_id');
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }
}
