<?php

namespace App\Shared\Http\Middleware;

use App\Shared\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resuelve el tenant de la tienda pública (visitantes sin login) contra un
 * slug fijo de configuración (config('tenant.slug_publico_por_defecto')),
 * porque hoy solo existe un tenant real (el piloto).
 *
 * Cuando exista más de un tenant sirviendo tienda pública (Fase 5, SaaS),
 * esto se reemplaza por resolución real por dominio/subdominio — ver
 * docs/architecture/apis.md. Mientras tanto, este middleware es la única
 * pieza que hay que cambiar; nada más en el sistema depende de cómo se
 * resuelve el tenant, solo de que quede resuelto (mismo TenantScope que usa
 * el panel administrativo).
 */
class ResolvePublicTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Tenant::where('slug', config('tenant.slug_publico_por_defecto'))->firstOrFail();

        app()->instance('currentTenantId', $tenant->id);
        app()->instance('currentTenant', $tenant);

        return $next($request);
    }
}
