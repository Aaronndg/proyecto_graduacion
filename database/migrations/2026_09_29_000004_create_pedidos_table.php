<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tabla 62: Pedidos (+ id_emprendedor)
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->increments('id_pedido');
            $table->unsignedInteger('id_emprendedor');
            $table->dateTime('fecha');
            $table->decimal('total', 10, 2)->default(0);
            $table->unsignedInteger('id_cliente');
            $table->unsignedInteger('id_estado');
            $table->timestamps();

            $table->foreign('id_emprendedor')->references('id_usuario')->on('usuarios')->restrictOnDelete();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->restrictOnDelete();
            $table->foreign('id_estado')->references('id_estado')->on('estados_pedido')->restrictOnDelete();
            $table->index(['id_emprendedor', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
