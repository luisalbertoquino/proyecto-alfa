<?php

namespace App\Shared\Http\Controllers;

use App\Shared\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Contenido institucional del negocio (Quiénes somos, datos de contacto).
 * `show` es público (lo consume la tienda); `update` requiere login.
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
        ];
    }
}
