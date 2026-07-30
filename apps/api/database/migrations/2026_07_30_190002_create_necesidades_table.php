<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Necesidad de piel" (acné, manchas, poros...) — una segunda forma de
 * clasificar el producto, además de Categoria. Un producto puede tener
 * varias (ej. un sérum de niacinamida sirve para "acné" y "poros" a la vez).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('necesidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('nombre');
            $table->string('slug');
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        // Tabla pivote: no lleva tenant_id propio porque ambos lados
        // (productos, necesidades) ya están scopeados por tenant — agregarlo
        // aquí sería duplicar el dato sin beneficio real.
        Schema::create('producto_necesidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained()->cascadeOnDelete();
            $table->foreignId('necesidad_id')->constrained('necesidades')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['producto_id', 'necesidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_necesidad');
        Schema::dropIfExists('necesidades');
    }
};
