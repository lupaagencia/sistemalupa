<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComprobanteContable extends Model
{
    protected $table = 'comprobantes_contables';

    protected $fillable = [
        'tipo',
        'numero',
        'fecha',
        'descripcion',
        'user_id'
    ];

    public function detalles()
    {
        return $this->hasMany('App\AsientoDetalle', 'comprobante_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * Check if the journal entry is balanced (sum of debits equals sum of credits).
     *
     * @return bool
     */
    public function isBalanced()
    {
        $debeSum = (float) $this->detalles()->sum('debe');
        $haberSum = (float) $this->detalles()->sum('haber');
        return abs($debeSum - $haberSum) < 0.001;
    }
}
