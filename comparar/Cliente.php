<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'id', 'tipo_cliente','razonsocial','telefono','direccionf','ciudad','departamento','pais','contacto','telefono_contacto','email_contacto',
        'tipo_documento', 'num_documento', 'email'
    ];
    public $timestamps = true;
    public function contactos(){
        return $this->belongsToMany(Contacto::class, 'cliente_contacto')->orderBy('favorito', 'desc');
    }
    public function contactoFavorito(){
        return $this->belongsToMany(Contacto::class, 'cliente_contacto')->where('favorito','=', 1);
    }
    public function empresas(){
        return $this->belongsToMany(Facturacion::class, 'cliente_factura')->orderBy('favorito', 'desc');
    }
    public function envios(){
        return $this->belongsToMany(Datosenvio::class, 'cliente_envio')->orderBy('favorito', 'desc');
    }
    public function orden(){
        return $this->hasMany(Ordentrabajo::class);
    }  //

    public function comprobante(){
        return $this->hasMany(Comprobante::class)->where('estado','=',3);
    }
   
   

}
