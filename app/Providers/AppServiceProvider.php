<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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
    }
}
