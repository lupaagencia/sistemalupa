<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PeriodoContable extends Model
{
    protected $table = 'periodos_contables';

    protected $fillable = [
        'mes',
        'anio',
        'estado'
    ];

    /**
     * Helper to verify if a given date is in a closed period.
     *
     * @param string $date
     * @return bool
     */
    public static function isClosed($date)
    {
        if (empty($date)) {
            return false;
        }

        $time = strtotime($date);
        $mes = (int)date('m', $time);
        $anio = (int)date('Y', $time);

        $periodo = self::where('mes', $mes)
            ->where('anio', $anio)
            ->first();

        return $periodo && $periodo->estado === 'Cerrado';
    }
}
