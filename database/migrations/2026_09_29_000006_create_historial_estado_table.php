<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tabla 64: Historial_Estado (+ id_usuario: quién realizó el cambio, Figura 40)
    public function up(): void
    {
        Schema::create('historial_estado', function (Blueprint $table) {
            $table->increments('id_historial');
            $table->unsignedInteger('id_pedido');
            $table->unsignedInteger('id_estado');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->dateTime('fecha_hora');
            $table->string('observacion', 255)->nullable();

            $table->foreign('id_pedido')->references('id_pedido')->on('pedidos')->cascadeOnDelete();
            $table->foreign('id_estado')->references('id_estado')->on('estados_pedido')->restrictOnDelete();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_estado');
    }
};
