<?php

namespace App\Modules\Pedidos\Exceptions;

use RuntimeException;

/**
 * Ver docs/architecture/apis.md: forma estándar de error, código
 * STOCK_INSUFICIENTE ya fijado ahí como ejemplo. El render a JSON vive
 * centralizado en bootstrap/app.php (withExceptions), no en esta clase.
 */
class StockInsuficienteException extends RuntimeException
{
    public function __construct(
        public readonly int $productoId,
        public readonly string $nombreProducto,
        public readonly int $disponible,
    ) {
        parent::__construct("No hay unidades suficientes de \"{$nombreProducto}\".");
    }
}
