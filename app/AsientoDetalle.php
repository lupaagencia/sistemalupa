<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AsientoDetalle extends Model
{
    protected $table = 'asientos_detalles';

    protected $fillable = [
        'comprobante_id',
        'cuenta_id',
        'tercero_id',
        'debe',
        'haber',
        'referencia'
    ];

    public function comprobante()
    {
        return $this->belongsTo('App\ComprobanteContable', 'comprobante_id');
    }

    public function cuenta()
    {
        return $this->belongsTo('App\Cuenta', 'cuenta_id');
    }

    public function tercero()
    {
        return $this->belongsTo('App\Persona', 'tercero_id');
    }
}
