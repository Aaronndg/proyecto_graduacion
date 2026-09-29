<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tabla 63: Detalle_Pedido
    public function up(): void
    {
        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->increments('id_detalle');
            $table->unsignedInteger('id_pedido');
            $table->unsignedInteger('id_producto');
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);

            $table->foreign('id_pedido')->references('id_pedido')->on('pedidos')->cascadeOnDelete();
            $table->foreign('id_producto')->references('id_producto')->on('productos')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido');
    }
};
