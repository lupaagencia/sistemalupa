<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $table = 'sliders';

    protected $fillable = [
        'titulo',
        'subtitulo',
        'imagen',
        'imagen_movil',
        'texto_boton',
        'url_boton',
        'ubicacion',
        'orden',
        'alto_slider',
        'estado'
    ];
}
