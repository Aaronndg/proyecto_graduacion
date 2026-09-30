<?php

use App\Models\Cliente;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Código que el emprendedor entrega al cliente para que vincule su cuenta con sus pedidos (RN-06).
     * Reemplaza la vinculación automática por correo, que no comprobaba la identidad del cliente.
     */
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('codigo_vinculacion', 8)->nullable()->unique()->after('id_usuario');
        });

        DB::table('clientes')->whereNull('id_usuario')->orderBy('id_cliente')->each(function ($cliente) {
            DB::table('clientes')->where('id_cliente', $cliente->id_cliente)
                ->update(['codigo_vinculacion' => Cliente::generarCodigo()]);
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique(['codigo_vinculacion']);
            $table->dropColumn('codigo_vinculacion');
        });
    }
};
