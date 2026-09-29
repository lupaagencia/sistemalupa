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
        'soporte'
    ];

    public function cuentaPorPagar()
    {
        return $this->belongsTo(CuentaPorPagar::class, 'cuenta_por_pagar_id');
    }
}
