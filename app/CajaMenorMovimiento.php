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

    public static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            static::recalculateBalances();
        });

        static::deleted(function ($model) {
            static::recalculateBalances();
        });
    }

    public static function recalculateBalances()
    {
        $movimientos = self::orderBy('fecha', 'asc')->orderBy('id', 'asc')->get();
        $saldo = 0.0;
        foreach ($movimientos as $mov) {
            if ($mov->tipo === 'Ingreso') {
                $saldo += (float)$mov->monto;
            } else {
                $saldo -= (float)$mov->monto;
            }
            if (abs((float)$mov->saldo_resultante - $saldo) > 0.001) {
                $mov->saldo_resultante = $saldo;
                $mov->saveQuietly();
            }
        }
    }
}
