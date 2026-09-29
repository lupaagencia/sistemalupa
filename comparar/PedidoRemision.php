<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoRemision extends Model
{
    protected $table = 'pedidos_remision';
    protected $fillable = ['pedido_id', 'remision_id', 'cuentacobro_id'];

    public function pedido()
    {
        return $this->belongsTo(Comprobante::class, 'pedido_id');
    }

    public function remision()
    {
        return $this->belongsTo(Comprobante::class, 'remision_id');
    }

    public function cuentacobro()
    {
        return $this->belongsTo(Comprobante::class, 'cuentacobro_id');
    }
}
