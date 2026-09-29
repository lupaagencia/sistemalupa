<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Egresos extends Model
{
    protected $table = 'egresos';

    protected $fillable = [
        'tipo_egreso',
        'concepto',
        'valor',
        'fecha',
        'metodo_pago',
        'beneficiario',
        'soporte',
        'cuenta_por_pagar_id',
        'abono_id',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function cuentaPorPagar()
    {
        return $this->belongsTo('App\CuentaPorPagar', 'cuenta_por_pagar_id');
    }

    public function abono()
    {
        return $this->belongsTo('App\AbonoCuentaPorPagar', 'abono_id');
    }
}

