<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CrmScopeTrait;

class CrmCotizacion extends Model
{
    use CrmScopeTrait;
    protected $table = 'crm_cotizaciones';

    protected $fillable = [
        'numero_cotizacion',
        'prospecto_id',
        'cliente_id',
        'oportunidad_id',
        'user_id',
        'fecha_emision',
        'fecha_vencimiento',
        'subtotal',
        'descuento',
        'iva',
        'total',
        'estado',
        'condiciones_pago',
        'observaciones',
        'pedido_id'
    ];

    public function prospecto()
    {
        return $this->belongsTo(CrmProspecto::class, 'prospecto_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function oportunidad()
    {
        return $this->belongsTo(CrmOportunidad::class, 'oportunidad_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles()
    {
        return $this->hasMany(CrmCotizacionDetalle::class, 'cotizacion_id');
    }
}
