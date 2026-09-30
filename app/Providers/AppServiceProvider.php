<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // URL en español: /usuarios/crear, /usuarios/{id}/editar
        Route::resourceVerbs(['create' => 'crear', 'edit' => 'editar']);

        // En desarrollo, detecta asignaciones de atributos no permitidos y consultas N+1.
        Model::shouldBeStrict(! $this->app->isProduction());

        // RNF-02: regla única para todas las contraseñas (registro, perfil y administración).
        // Máximo 72 caracteres porque bcrypt ignora lo que pasa de ese límite.
        Password::defaults(fn () => Password::min(8)->max(72)->letters()->numbers());
    }
}
