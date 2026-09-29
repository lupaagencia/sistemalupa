<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticuloTroquel extends Model
{
    protected $table = 'articulo_troquels';
    protected $fillable = ['articulo_id', 'cabida', 'imagen', 'ancho_impresion', 'largo_impresion'];

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }
}
