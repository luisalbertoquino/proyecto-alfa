<?php

namespace App\Shared\Http\Controllers;

use App\Shared\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Contenido institucional del negocio (Quiénes somos, datos de contacto,
 * theming reducido: color de marca y tipografía). `show` es público (lo
 * consume la tienda en cada carga de página); `update` requiere login.
 */
class NegocioController
{
    public function show(): JsonResponse
    {
        $tenant = Tenant::findOrFail(app('currentTenantId'));

        return response()->json(['data' => $this->formatear($tenant)]);
    }

    public function update(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'quienes_somos' => ['nullable', 'string'],
            'contacto_whatsapp' => ['nullable', 'string', 'max:30'],
            'contacto_email' => ['nullable', 'email', 'max:255'],
            'contacto_horario' => ['nullable', 'string', 'max:255'],
            'color_primario' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'tipografia' => ['nullable', Rule::in(['sans', 'serif'])],
        ]);

        $tenant = Tenant::findOrFail(app('currentTenantId'));
        $tenant->update($datos);

        return response()->json(['data' => $this->formatear($tenant)]);
    }

    private function formatear(Tenant $tenant): array
    {
        return [
            'nombre' => $tenant->nombre,
            'quienes_somos' => $tenant->quienes_somos,
            'contacto_whatsapp' => $tenant->contacto_whatsapp,
            'contacto_email' => $tenant->contacto_email,
            'contacto_horario' => $tenant->contacto_horario,
            'color_primario' => $tenant->color_primario,
            'tipografia' => $tenant->tipografia,
        ];
    }
}
