<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tabla 61: Estados_Pedido (+ orden para presentar el flujo)
    public function up(): void
    {
        Schema::create('estados_pedido', function (Blueprint $table) {
            $table->increments('id_estado');
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->unsignedTinyInteger('orden')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados_pedido');
    }
};
