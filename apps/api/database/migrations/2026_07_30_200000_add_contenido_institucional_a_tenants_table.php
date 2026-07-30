<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contenido institucional de la tienda (Quiénes somos, datos de contacto).
 * Vive en Tenant porque es "info del negocio", no una entidad propia — con
 * 4 campos no se justifica una tabla de contenido genérica todavía.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->text('quienes_somos')->nullable()->after('slug');
            $table->string('contacto_whatsapp')->nullable()->after('quienes_somos');
            $table->string('contacto_email')->nullable()->after('contacto_whatsapp');
            $table->string('contacto_horario')->nullable()->after('contacto_email');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['quienes_somos', 'contacto_whatsapp', 'contacto_email', 'contacto_horario']);
        });
    }
};
