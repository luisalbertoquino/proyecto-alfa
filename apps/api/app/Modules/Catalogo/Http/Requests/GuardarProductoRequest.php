<?php

namespace App\Modules\Catalogo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GuardarProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la autorización de "quién puede administrar" ya la resuelve el middleware auth:sanctum de la ruta.
    }

    public function rules(): array
    {
        return [
            'categoria_id' => [
                'nullable',
                Rule::exists('categorias', 'id')->where('tenant_id', app('currentTenantId')),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:100'],
            'imagen_url' => ['nullable', 'url', 'max:2048'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'activo' => ['boolean'],
        ];
    }

    /**
     * El slug se deriva del nombre, no lo manda el cliente — evita slugs
     * inconsistentes o inventados a mano.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'activo' => $this->boolean('activo', true),
        ]);
    }

    public function slug(): string
    {
        return Str::slug($this->input('nombre'));
    }
}
