<?php

namespace App\Shared\Http\Middleware;

use App\Shared\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resuelve el tenant de la tienda pública (visitantes sin login) por el Host
 * de la petición contra `tenants.dominio_api` (el subdominio de API propio
 * del tenant — ver esa columna para la nuance de por qué no es el dominio
 * del storefront). Si el Host no coincide con ningún tenant (el caso del
 * piloto hoy, que no tiene `dominio_api` configurado, o cualquier host
 * desconocido), cae al slug fijo de configuración
 * (config('tenant.slug_publico_por_defecto')) como red de seguridad
 * permanente — nunca rompe la tienda pública por un host no registrado.
 *
 * Nada más en el sistema depende de cómo se resuelve el tenant, solo de que
 * quede resuelto (mismo TenantScope que usa el panel administrativo).
 */
class ResolvePublicTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Tenant::where('dominio_api', $request->getHost())->first()
            ?? Tenant::where('slug', config('tenant.slug_publico_por_defecto'))->firstOrFail();

        app()->instance('currentTenantId', $tenant->id);
        app()->instance('currentTenant', $tenant);

        return $next($request);
    }
}
