<?php

namespace NovaListCard\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Nova\NovaCoreServiceProvider;
use NovaListCard\Tests\Fixtures\NovaServiceProvider;
use Orchestra\Testbench\Database\MigrateProcessor;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            NovaCoreServiceProvider::class,
            NovaServiceProvider::class,
            \NovaListCard\ServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();

        $migrator = new MigrateProcessor($this, [
            '--path'     => __DIR__.'/Fixtures/migrations',
            '--realpath' => true,
        ]);
        $migrator->up();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}
