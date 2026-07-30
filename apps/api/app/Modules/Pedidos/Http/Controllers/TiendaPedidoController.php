<?php

namespace App\Modules\Pedidos\Http\Controllers;

use App\Modules\Pedidos\Http\Requests\CrearPedidoRequest;
use App\Modules\Pedidos\Services\CrearPedidoService;
use Illuminate\Http\JsonResponse;

class TiendaPedidoController
{
    public function __construct(
        private readonly CrearPedidoService $crearPedido,
    ) {}

    public function store(CrearPedidoRequest $request): JsonResponse
    {
        $pedido = $this->crearPedido->ejecutar(
            $request->validated('cliente'),
            $request->validated('items'),
        );

        return response()->json(['data' => $pedido], 201);
    }
}
