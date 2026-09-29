<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackfillPedidoId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financial:backfill-pedido-id';
    protected $description = 'Pobla el campo pedido_id en comprobantes y recibos existentes';

    public function handle()
    {
        $this->info('Poblando Comprobantes...');
        $docs = \App\Comprobante::whereIn('tipo', ['cuentacobro', 'remision', 'factura'])->whereNull('pedido_id')->get();
        foreach ($docs as $doc) {
            $pid = $doc->getPedidoPadreId();
            if ($pid) {
                $doc->pedido_id = $pid;
                $doc->save();
                $this->output->write('.');
            }
        }
        $this->info('\nPoblando Recibos...');
        $recibos = \App\ReciboPago::whereNull('pedido_id')->get();
        foreach ($recibos as $rec) {
            if ($rec->comprobante_id) {
                $t = \App\Comprobante::find($rec->comprobante_id);
                if ($t) {
                    $pid = $t->getPedidoPadreId();
                    if ($pid) {
                        $rec->pedido_id = $pid;
                        $rec->save();
                        $this->output->write('.');
                    }
                }
            }
        }
        $this->info('\nRecalculando saldos de pedidos...');
        $pedidos = \App\Comprobante::where('tipo', 'pedido')->get();
        $controller = new \App\Http\Controllers\OrdentrabajoController();
        foreach ($pedidos as $ped) {
            $controller->propagarPagoAPedido($ped, 0);
            $this->output->write('.');
        }
        $this->info('\nFinalizado con éxito.');
        return 0;
    }
}
