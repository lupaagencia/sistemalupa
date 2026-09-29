<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Comprobante;

class PedidoProduccionMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Comprobante $pedido)
    {
        $pedido->load('lineas.orden.detalles');
        $this->pedido = $pedido;
        $this->subject = 'Nuevo Pedido en Producción - #' . $pedido->id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.pedido-produccion');
    }
}
