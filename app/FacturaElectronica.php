<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FacturaElectronica extends Model
{
    protected $table = 'facturas_electronicas';

    protected $fillable = [
        'comprobante_id',
        'cufe',
        'uuid_proveedor',
        'estado_dian',
        'xml_path',
        'pdf_path',
        'qr_code',
        'dian_response',
        'fecha_transmision',
    ];

    protected $dates = [
        'fecha_transmision',
    ];

    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
    }
}
