<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla 57: Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id_rol');
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 255)->nullable();
        });

        // Tabla 58: Usuarios (+ negocio y activo para la administración de cuentas)
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->string('nombre', 100);
            $table->string('correo', 150)->unique();
            $table->string('contrasena', 255);
            $table->unsignedInteger('id_rol');
            $table->string('negocio', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('id_rol')->references('id_rol')->on('roles')->restrictOnDelete();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('roles');
    }
};
