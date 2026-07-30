<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant público por defecto
    |--------------------------------------------------------------------------
    |
    | El piloto de Proyecto Alfa opera un solo tenant. La tienda pública
    | (apps/web) resuelve el tenant actual contra este slug fijo mientras no
    | exista resolución real por dominio (varios tenants, cada uno con su
    | propio subdominio o dominio propio — Fase 5, SaaS).
    |
    | Ver App\Shared\Http\Middleware\ResolvePublicTenant y
    | docs/architecture/apis.md.
    |
    */

    'slug_publico_por_defecto' => env('TENANT_PUBLICO_SLUG', 'skincare-piloto'),

];
