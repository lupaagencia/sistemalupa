<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionAsistencia extends Model
{
    protected $table = 'configuracion_asistencia';

    protected $fillable = [
        'latitud_empresa',
        'longitud_empresa',
        'radio_maximo_metros',
        'requerir_gps',
        'pin_kiosco'
    ];
}
