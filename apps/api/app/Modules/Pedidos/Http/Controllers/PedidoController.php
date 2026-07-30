<?php

namespace App\Modules\Pedidos\Http\Controllers;

use App\Modules\Pedidos\Models\Pedido;
use App\Modules\Pedidos\Services\CancelarPedidoService;
use App\Modules\Pedidos\Services\ConfirmarPedidoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedidoController
{
    public function __construct(
        private readonly ConfirmarPedidoService $confirmarPedido,
        private readonly CancelarPedidoService $cancelarPedido,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $pedidos = Pedido::with('cliente')
            ->withCount('detalles')
            ->when($request->query('estado'), fn ($q, $estado) => $q->where('estado', $estado))
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'data' => $pedidos->items(),
            'meta' => [
                'version' => 'v1',
                'pagina_actual' => $pedidos->currentPage(),
                'total' => $pedidos->total(),
                'por_pagina' => $pedidos->perPage(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $pedido = Pedido::with('cliente', 'detalles.producto')->findOrFail($id);

        return response()->json(['data' => $pedido]);
    }

    public function confirmar(int $id): JsonResponse
    {
        $pedido = Pedido::findOrFail($id);

        return response()->json(['data' => $this->confirmarPedido->ejecutar($pedido)]);
    }

    public function cancelar(int $id): JsonResponse
    {
        $pedido = Pedido::findOrFail($id);

        return response()->json(['data' => $this->cancelarPedido->ejecutar($pedido)]);
    }
}
