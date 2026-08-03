<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Borrado suave para productos y rutinas: un registro referenciado por un
 * pedido real (`detalle_pedidos.producto_id`, `restrictOnDelete()`) nunca
 * debe desaparecer de la base de datos, solo ocultarse — perder la fila
 * rompería el historial de ese pedido y no hay garantía legal de que un
 * producto vendido pueda borrarse sin más. Ver docs/estado-actual.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('rutinas', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('rutinas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
