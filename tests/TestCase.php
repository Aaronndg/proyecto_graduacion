<?php

namespace Tests;

use Database\Seeders\CatalogosSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Cada prueba con RefreshDatabase inicia con roles y estados cargados. */
    protected string $seeder = CatalogosSeeder::class;

    protected bool $seed = true;
}
