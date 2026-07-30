<?php

namespace App\Modules\Catalogo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarRutinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'productos' => ['required', 'array', 'min:1'],
            'productos.*' => [
                Rule::exists('productos', 'id')->where('tenant_id', app('currentTenantId')),
            ],
        ];
    }

    /**
     * @return int[]
     */
    public function productosOrdenados(): array
    {
        return $this->input('productos', []);
    }
}
