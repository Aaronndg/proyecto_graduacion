<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Dirección pública del catálogo del negocio (p. ej. /catalogo/dulces-maria). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('catalogo', 80)->nullable()->unique()->after('logo');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropUnique(['catalogo']);
            $table->dropColumn('catalogo');
        });
    }
};
