<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Theming reducido por tenant: color de marca y tipografía, leídos por
 * apps/web una sola vez en el layout raíz y aplicados como variable CSS
 * (ver App\Shared\Http\Controllers\NegocioController y
 * docs/design/design-system.md — esta es la versión mínima de lo que ese
 * documento describe como sistema de tokens completo).
 *
 * Ambos nullable: un tenant sin configurar cae al negro por defecto
 * (apps/web/src/app/globals.css) y tipografía sans — nunca se rompe la
 * tienda por falta de tema, mismo criterio que el resto del proyecto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('color_primario', 7)->nullable()->after('dominio_api');
            $table->string('tipografia', 10)->nullable()->after('color_primario');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['color_primario', 'tipografia']);
        });
    }
};
