<?php

use Illuminate\Support\Facades\Schedule;

// Copia diaria de la base de datos (se conservan las últimas RESPALDOS_CONSERVAR).
// En el alojamiento, una tarea cron ejecuta cada minuto: php artisan schedule:run
Schedule::command('respaldo:crear')->dailyAt('03:00');
