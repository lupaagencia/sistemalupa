<?php

namespace App\Exports;

use App\Ordentrabajo;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrdenesExport implements FromCollection
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Ordentrabajo::join('clientes', 'ordentrabajos.idcliente', '=', 'clientes.id')->join('costos', 'ordentrabajos.id', '=', 'costos.ordentrabajo_id')->join('costois', 'costos.costois_id', '=', 'costois.id')->join('articulos', 'ordentrabajos.idarticulo', '=', 'articulos.id')
            ->select('*', 'articulos.nombre as articulo', 'clientes.razonsocial as cliente', 'costos.id as idcosto', 'costos.descripcion as descripcion_costo', 'costois.nombre as nombre_insumo', 'ordentrabajos.created_at as fechaorden', 'ordentrabajos.updated_at as updateorden', 'ordentrabajos.id as idorden', 'ordentrabajos.estado as estadoc', 'ordentrabajos.produccion as estadop')
            ->where('costos.titulo', 'papel')->where('costois.idpersona', 'LIKE', '%%')->orderBy('costos.descripcion', 'desc')->get();
    }
}
