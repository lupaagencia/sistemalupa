<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AbonoCuentaPorPagar extends Model
{
    protected $table = 'abonos_cuentas_por_pagar';
    protected $fillable = [
        'cuenta_por_pagar_id', 
        'monto', 
        'fecha', 
        'observaciones',
        'soporte',
        'comprobante_id'
    ];

    public function comprobante()
    {
        return $this->belongsTo(ComprobanteContable::class, 'comprobante_id');
    }

    public function cuentaPorPagar()
    {
        return $this->belongsTo(CuentaPorPagar::class, 'cuenta_por_pagar_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($abono) {
            if ($abono->cuentaPorPagar) {
                CuentaPorPagar::cruzarSaldosAFavor(
                    $abono->cuentaPorPagar->activo_id,
                    $abono->cuentaPorPagar->proveedor_id
                );
            }
        });

        static::deleted(function ($abono) {
            // Solo auto-cruzar saldos si se elimino un abono real (efectivo/banco/caja),
            // NO al eliminar manualmente un abono de cruce 'Saldo a Favor' (para evitar que se vuelva a crear de inmediato)
            if ($abono->metodo_pago !== 'Saldo a Favor' && $abono->cuentaPorPagar) {
                CuentaPorPagar::cruzarSaldosAFavor(
                    $abono->cuentaPorPagar->activo_id,
                    $abono->cuentaPorPagar->proveedor_id
                );
            }
        });
    }
}
