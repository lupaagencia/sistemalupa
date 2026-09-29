<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entrega extends Model
{
    protected $table = 'entregas';
    public $timestamps = true;
    protected $fillable = [
        'ordentrabajo_id',
        'pedido_id',
        'fecha',
        'numero_remision',
        'observaciones',
        'user_id',
        'cantidad',
        'saldo_anterior',
        'saldo_restante',
        'tipo_documento',
        'comprobante_id',
        'cuentacobro_id'
    ];

    public function ordentrabajo()
    {
        return $this->belongsTo(Ordentrabajo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class);
    }

    public function cuentacobro()
    {
        return $this->belongsTo(Comprobante::class , 'cuentacobro_id');
    }

    public function entregaItems()
    {
        return $this->hasMany(EntregaItems::class , 'ordentrabajo_id');
    }


}
