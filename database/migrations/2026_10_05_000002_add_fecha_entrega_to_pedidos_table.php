<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Para cuándo hay que tener listo el pedido (opcional). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->date('fecha_entrega')->nullable()->after('fecha');
            $table->index(['id_emprendedor', 'fecha_entrega']);
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex(['id_emprendedor', 'fecha_entrega']);
            $table->dropColumn('fecha_entrega');
        });
    }
};
