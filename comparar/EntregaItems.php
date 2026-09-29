<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EntregaItems extends Model
{
    protected $table = 'entrega_items';
    protected $fillable = [
        'entrega_id',
        'linea_comprobante_id',
        'cantidad',
    ];

    public function entrega()
    {
        return $this->belongsTo(Entrega::class , 'entrega_id');
    }

    public function linea()
    {
        return $this->belongsTo(LineaComprobante::class , 'linea_comprobante_id');
    }
}
