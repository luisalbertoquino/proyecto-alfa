<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained()->restrictOnDelete();
            // Estados posibles (ver docs/business/reglas-de-negocio.md): pendiente_pago,
            // pendiente_stock, confirmado, despachado, entregado, cancelado.
            // String simple (no enum de MySQL) para no requerir ALTER TABLE al agregar un estado.
            $table->string('estado', 30)->default('pendiente_pago');
            $table->string('canal_origen', 30)->default('tienda_propia');
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'estado', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
