<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facturacion extends Model
{
    protected $table = 'datos_factura';
    protected $fillable = [
        'id', 'razonsocial','tipo_persona','tipo_documento','numero','digito','direccion','telefono','correo','ciudad','departamento','pais','actividad','responsable'
    ];
    public function clientes(){
        return $this->belongsToMany(Cliente::class, 'cliente_factura');
    }

}
