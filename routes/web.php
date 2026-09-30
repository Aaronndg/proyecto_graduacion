<?php

use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\Auth\SesionController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\MiPedidoController;
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\VinculacionController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ProductoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/panel');

Route::middleware('guest')->group(function () {
    Route::get('login', [SesionController::class, 'create'])->name('login');
    Route::post('login', [SesionController::class, 'store'])->name('login.store');
    Route::get('registro', [RegistroController::class, 'create'])->name('registro');
    Route::post('registro', [RegistroController::class, 'store'])->middleware('throttle:10,1')->name('registro.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [SesionController::class, 'destroy'])->name('logout');

    Route::get('panel', PanelController::class)->name('panel');

    Route::get('perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('perfil/contrasena', [PerfilController::class, 'actualizarContrasena'])->name('perfil.contrasena');

    // Módulos de clientes, productos y pedidos (MOD-02, MOD-03) — solo emprendedor.
    // El trait PerteneceAEmprendedor garantiza que cada uno opere únicamente sobre sus datos.
    Route::middleware('rol:emprendedor')->group(function () {
        Route::resource('clientes', ClienteController::class);
        Route::resource('productos', ProductoController::class)->except('show');
        Route::resource('pedidos', PedidoController::class)->except('destroy');
        Route::post('pedidos/{pedido}/estado', [PedidoController::class, 'cambiarEstado'])->name('pedidos.estado');
        Route::get('seguimiento', SeguimientoController::class)->name('seguimiento');
        Route::post('clientes/{cliente}/codigo', [VinculacionController::class, 'regenerar'])->name('clientes.codigo');
    });

    // Seguimiento de pedidos por parte del cliente (RF-10) — solo lectura.
    Route::middleware('rol:cliente')->group(function () {
        Route::get('mis-pedidos/{pedido}', [MiPedidoController::class, 'show'])->name('mis-pedidos.show');
        Route::post('vincular', [VinculacionController::class, 'store'])->name('vincular');
    });

    // Módulo de usuarios y acceso (MOD-01) — solo administrador.
    Route::middleware('rol:administrador')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show', 'destroy']);
        Route::patch('usuarios/{usuario}/estado', [UsuarioController::class, 'alternarEstado'])->name('usuarios.estado');
    });
});
