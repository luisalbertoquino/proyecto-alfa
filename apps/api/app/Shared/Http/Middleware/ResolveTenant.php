<?php

namespace App\Shared\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resuelve el tenant actual a partir del usuario autenticado (Sanctum) y lo
 * deja disponible en el contenedor como 'currentTenantId' para que
 * App\Shared\Scopes\TenantScope filtre automáticamente cada query.
 *
 * Ver docs/architecture/apis.md: el tenant se resuelve por token, nunca por
 * body/query. La resolución por dominio (tienda pública sin login) se agrega
 * cuando exista esa ruta (Semana 2).
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            app()->instance('currentTenantId', $request->user()->tenant_id);
        }

        return $next($request);
    }
}
