<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dominio por el que un tenant es identificable en la tienda pública. No es
 * el dominio del storefront (apps/web) sino el Host que la API recibe de
 * verdad: apps/web hace sus fetches contra NEXT_PUBLIC_API_URL, que apunta
 * al subdominio propio de la API de cada tenant (ej.
 * skincare-api.alegrarte.store) — no al dominio público de la tienda. Por
 * eso "resolución por dominio" en este despliegue compara contra el
 * subdominio de API de cada tenant, no contra el dominio visible al usuario
 * final. Ver App\Shared\Http\Middleware\ResolvePublicTenant.
 *
 * Nullable: el piloto no lo necesita todavía (sigue resuelto por el slug
 * fijo de configuración, que se mantiene como red de seguridad); se llena
 * cuando un tenant tiene su propio subdominio de API.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('dominio_api')->nullable()->unique()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('dominio_api');
        });
    }
};
