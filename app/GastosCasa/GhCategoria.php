<?php

namespace App\GastosCasa;

use Illuminate\Database\Eloquent\Model;

class GhCategoria extends Model
{
    protected $table = 'gh_categorias';

    protected $fillable = [
        'nombre',
        'tipo'
    ];

    public function gastos()
    {
        return $this->hasMany(GhGasto::class, 'categoria_id');
    }
}
