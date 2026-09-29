<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CajaMenorMovimiento extends Model
{
    protected $table = 'caja_menor_movimientos';

    protected $fillable = [
        'tipo',
        'monto',
        'saldo_resultante',
        'fecha',
        'descripcion',
        'soporte',
        'egreso_id',
        'user_id'
    ];

    public function egreso()
    {
        return $this->belongsTo('App\Egresos', 'egreso_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
