<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tabla 60: Productos (+ id_emprendedor)
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->increments('id_producto');
            $table->unsignedInteger('id_emprendedor');
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();
            $table->decimal('precio', 10, 2);
            $table->boolean('estado')->default(true);
            $table->timestamps();

            $table->foreign('id_emprendedor')->references('id_usuario')->on('usuarios')->restrictOnDelete();
            $table->index(['id_emprendedor', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
