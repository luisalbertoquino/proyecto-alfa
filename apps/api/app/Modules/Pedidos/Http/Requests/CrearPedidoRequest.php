<?php

namespace App\Modules\Pedidos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // checkout de la tienda pública, no requiere login.
    }

    public function rules(): array
    {
        return [
            'cliente.nombre' => ['required', 'string', 'max:255'],
            'cliente.email' => ['required', 'email', 'max:255'],
            'cliente.telefono' => ['nullable', 'string', 'max:30'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
        ];
    }
}
