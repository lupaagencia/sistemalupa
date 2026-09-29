<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    protected $table = 'ingresos';

    protected $fillable = [
        'idproveedor', 
        'idusuario',
        'tipo_comprobante',
        'serie_comprobante',
        'num_comprobante',
        'fecha_hora',
        'impuesto',
        'total',
        'estado',
        'forma_pago',
        'dias_credito'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'idusuario');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'idproveedor');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleIngreso::class, 'idingreso');
    }

    public function cuentaPorPagar()
    {
        return $this->hasOne(CuentaPorPagar::class, 'ingreso_id');
    }
}
