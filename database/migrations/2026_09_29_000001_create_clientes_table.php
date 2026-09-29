<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tabla 59: Clientes (+ id_emprendedor: dueño del registro; id_usuario: cuenta de cliente vinculada)
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->increments('id_cliente');
            $table->unsignedInteger('id_emprendedor');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->string('nombre', 100);
            $table->string('telefono', 20)->nullable();
            $table->string('correo', 150)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_emprendedor')->references('id_usuario')->on('usuarios')->restrictOnDelete();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->nullOnDelete();
            // Evita clientes duplicados con el mismo correo dentro de un emprendimiento
            $table->unique(['id_emprendedor', 'correo']);
            $table->index(['id_emprendedor', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
