<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CostoProduccion extends Model
{
    protected $table = 'costos';
    protected $fillable = [
        'ordentrabajo_id','costois_id','titulo','descripcion','cantidad','valor','total','orden','completado','pago','fecha_termina','Terminado',
        'medida_material','tamano','medida_final','cabida','sobrante','componente'
    ];

    public function ordentrabajo()
    {
        return $this->belongsTo(Ordentrabajo::class, 'ordentrabajo_id');
    }

    public function personas()
    {
        return $this->belongsTo('App\Ordentrabajo');
    }
    public function proveedores()
    {
        return $this->belongsTo('App\Costois');
    }
    public function tipo()
    {
        return $this->belongsTo(TipoProducto::class);
    }
    public function costois(){
        return $this->belongsTo(Costois::class,'costois_id');

    }
    public function activo()
    {
        return $this->belongsTo(Activo::class, 'costois_id');
    }

    // Accessors for Fallback to Ordentrabajo level fields for single-paper legacy records
    public function getMedidaMaterialAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return $this->ordentrabajo ? $this->ordentrabajo->medida_material : null;
    }

    public function getTamanoAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return $this->ordentrabajo ? $this->ordentrabajo->tamano : null;
    }

    public function getMedidaFinalAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return $this->ordentrabajo ? $this->ordentrabajo->medida_final : null;
    }

    public function getCabidaAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return $this->ordentrabajo ? $this->ordentrabajo->cabida : null;
    }

    public function getSobranteAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return $this->ordentrabajo ? $this->ordentrabajo->carpeta_cliente : null;
    }
}

