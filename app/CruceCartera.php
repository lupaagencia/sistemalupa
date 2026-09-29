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

    public static function boot()
    {
        parent::boot();

        static::deleted(function ($cruce) {
            if ($cruce->comprobante_id) {
                $comp = Comprobante::find($cruce->comprobante_id);
                if ($comp) {
                    $totalCruces = (float) CruceCartera::whereHas('reciboPago')->where('comprobante_id', $comp->id)->sum('monto');
                    $comp->abono = round($totalCruces, 2);
                    $comp->saldo = max(0, round((float)$comp->total - $comp->abono, 2));
                    $comp->save();

                    $pids = $comp->getPedidoPadreIds();
                    $otController = new \App\Http\Controllers\OrdentrabajoController();
                    foreach ($pids as $pId) {
                        $otController->verificarYActualizarPedido($pId);
                    }
                }
            }
        });
    }
}
