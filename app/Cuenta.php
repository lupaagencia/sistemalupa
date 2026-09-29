<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    protected $table = 'cuentas';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'naturaleza',
        'es_detalle',
        'padre_id'
    ];

    public function padre()
    {
        return $this->belongsTo('App\Cuenta', 'padre_id');
    }

    public function subcuentas()
    {
        return $this->hasMany('App\Cuenta', 'padre_id');
    }

    public function detalles()
    {
        return $this->hasMany('App\AsientoDetalle', 'cuenta_id');
    }
}
