<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ajustes extends Model
{
    //protected $table='categorias';
    protected $fillable = ['tipo', 'detalle', 'valor', 'categoria'];
    public $timestamps = false;

    public static function getValorConfig($tipo, $detalle, $default = null)
    {
        $row = static::where('tipo', $tipo)->where('detalle', $detalle)->first();
        return ($row && $row->valor !== null && $row->valor !== '') ? $row->valor : $default;
    }

    public static function getAuxilioTransporte($default = 200000)
    {
        return (float) static::getValorConfig('nomina', 'auxilio_transporte', $default);
    }

    public static function getSalarioMinimo($default = 1400000)
    {
        return (float) static::getValorConfig('nomina', 'salario_minimo', $default);
    }
}
