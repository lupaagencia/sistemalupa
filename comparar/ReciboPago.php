<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReciboPago extends Model
{
    protected $table = 'recibo_pagos';
    protected $fillable = [
        'comprobante_id',
        'cliente_id',
        'user_id',
        'fecha',
        'monto',
        'saldo_recibo',
        'forma_pago',
        'num_recibo',
        'observaciones',
        'pedido_id'
    ];
    public function pedido()
    {
        return $this->belongsTo(Comprobante::class, 'pedido_id');
    }

    public function cruces()
    {
        return $this->hasMany(CruceCartera::class, 'recibo_pago_id');
    }

    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
