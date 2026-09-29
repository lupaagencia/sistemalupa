<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtractoBancario extends Model
{
    protected $table = 'extractos_bancarios';

    protected $fillable = [
        'fecha',
        'descripcion',
        'monto',
        'referencia',
        'estado',
        'comprobante_id'
    ];

    public function comprobante()
    {
        return $this->belongsTo(ComprobanteContable::class, 'comprobante_id');
    }
}
