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
        'pedido_id',
        'comprobante_contable_id'
    ];
    public function comprobanteContable()
    {
        return $this->belongsTo(ComprobanteContable::class, 'comprobante_contable_id');
    }
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

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($recibo) {
            // 1. Obtener cruces y comprobantes afectados
            $cruces = \App\CruceCartera::where('recibo_pago_id', $recibo->id)->get();
            $comprobanteIds = $cruces->pluck('comprobante_id')->filter()->unique()->toArray();

            // 2. Eliminar los cruces de cartera individualmente invocando eventos Eloquent
            foreach ($cruces as $cruce) {
                $cruce->delete();
            }

            // 3. Recalcular abono y saldo de todas las Cuentas de Cobro afectadas (excluyendo este recibo)
            foreach ($comprobanteIds as $cId) {
                $comp = Comprobante::find($cId);
                if ($comp) {
                    $totalCruces = (float) \App\CruceCartera::whereHas('reciboPago')
                        ->where('recibo_pago_id', '!=', $recibo->id)
                        ->where('comprobante_id', $comp->id)
                        ->sum('monto');
                    $comp->abono = round($totalCruces, 2);
                    $comp->saldo = max(0, round((float)$comp->total - $comp->abono, 2));
                    $comp->save();
                }
            }

            // 4. Recalcular todos los pedidos afectados (vía pedido_id o vía CC)
            $pedidoIds = [];
            if ($recibo->pedido_id) {
                $pedidoIds[] = $recibo->pedido_id;
            }
            foreach ($comprobanteIds as $cId) {
                $comp = Comprobante::find($cId);
                if ($comp) {
                    $pids = $comp->getPedidoPadreIds();
                    $pedidoIds = array_merge($pedidoIds, $pids);
                }
            }

            $pedidoIds = array_unique(array_filter($pedidoIds));
            $otController = new \App\Http\Controllers\OrdentrabajoController();
            foreach ($pedidoIds as $pId) {
                $otController->verificarYActualizarPedido($pId);
            }

            // 5. Eliminar comprobante contable si aplica
            if ($recibo->comprobante_contable_id) {
                $compC = \App\ComprobanteContable::find($recibo->comprobante_contable_id);
                if ($compC) {
                    $compC->delete();
                }
            }
        });
    }
}
