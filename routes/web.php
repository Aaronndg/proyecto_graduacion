<?php

use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\Auth\SesionController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PerfilController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/panel');

Route::middleware('guest')->group(function () {
    Route::get('login', [SesionController::class, 'create'])->name('login');
    Route::post('login', [SesionController::class, 'store'])->name('login.store');
    Route::get('registro', [RegistroController::class, 'create'])->name('registro');
    Route::post('registro', [RegistroController::class, 'store'])->name('registro.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [SesionController::class, 'destroy'])->name('logout');

    Route::get('panel', PanelController::class)->name('panel');

    Route::get('perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('perfil/contrasena', [PerfilController::class, 'actualizarContrasena'])->name('perfil.contrasena');

    // Módulo de usuarios y acceso (MOD-01) — solo administrador.
    Route::middleware('rol:administrador')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show', 'destroy']);
        Route::patch('usuarios/{usuario}/estado', [UsuarioController::class, 'alternarEstado'])->name('usuarios.estado');
    });
});
