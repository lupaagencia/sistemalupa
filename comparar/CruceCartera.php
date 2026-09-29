<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CruceCartera extends Model
{
    protected $table = 'cruce_cartera';
    protected $fillable = [
        'recibo_pago_id',
        'comprobante_id',
        'monto',
        'fecha_cruce'
    ];

    public function reciboPago()
    {
        return $this->belongsTo(ReciboPago::class, 'recibo_pago_id');
    }

    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
    }
}
