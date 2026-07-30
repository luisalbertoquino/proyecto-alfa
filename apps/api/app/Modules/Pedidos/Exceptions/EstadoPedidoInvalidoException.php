<?php

namespace App\Modules\Pedidos\Exceptions;

use RuntimeException;

class EstadoPedidoInvalidoException extends RuntimeException
{
    public function __construct(
        public readonly int $pedidoId,
        public readonly string $estadoActual,
        string $mensaje,
    ) {
        parent::__construct($mensaje);
    }
}
