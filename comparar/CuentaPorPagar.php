<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CuentaPorPagar extends Model
{
    protected $table = 'cuentas_por_pagar';
    protected $fillable = [
        'activo_id', 
        'proveedor_id', 
        'ordentrabajo_id', 
        'costo_id', 
        'ingreso_id', 
        'numero_factura',
        'descripcion', 
        'cantidad', 
        'valor_unitario', 
        'monto', 
        'saldo', 
        'estado', 
        'soporte',
        'fecha',
        'fecha_vencimiento'
    ];

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function orden()
    {
        return $this->belongsTo(Ordentrabajo::class, 'ordentrabajo_id');
    }

    public function costo()
    {
        return $this->belongsTo(CostoProduccion::class, 'costo_id');
    }

    public function ingreso()
    {
        return $this->belongsTo(Ingreso::class, 'ingreso_id');
    }

    public function abonos()
    {
        return $this->hasMany(AbonoCuentaPorPagar::class, 'cuenta_por_pagar_id');
    }
}
