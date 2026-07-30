<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ver docs/architecture/apis.md "Rate limiting": límite más generoso
        // para tráfico real de compradores en la tienda pública, por IP
        // (no hay usuario autenticado que identifique al tenant ahí).
        RateLimiter::for('tienda', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        // Crear un pedido es una operación de negocio, no una simple
        // lectura: límite más estricto para contener spam de pedidos falsos.
        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Panel administrativo: por usuario autenticado (o IP si aún no hay
        // sesión), no solo por IP — varios tenants pueden compartir salida a
        // internet (NAT, oficina compartida) sin deberse limitar entre sí.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });
    }
}
