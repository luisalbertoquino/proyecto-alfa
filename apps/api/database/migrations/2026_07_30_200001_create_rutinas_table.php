<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rutina sugerida (ej. "Rutina de 3 pasos"): una lista ordenada de
 * productos. Ver docs/estado-actual.md — inspirado en rosavainilla.co.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rutinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('nombre');
            $table->string('slug');
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        // Pivote: no lleva tenant_id propio (mismo criterio que
        // producto_necesidad — ambos lados ya están scopeados por tenant).
        Schema::create('rutina_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rutina_id')->constrained()->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->unique(['rutina_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rutina_producto');
        Schema::dropIfExists('rutinas');
    }
};
