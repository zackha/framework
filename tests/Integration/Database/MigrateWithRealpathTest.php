<?php

namespace Illuminate\Tests\Integration\Database;

use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;

class MigrateWithRealpathTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        if (! env('DB_CONNECTION')) {
            $app['config']->set('database.default', 'testbench');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->app['config']->get('database.default') !== 'testbench') {
            $this->artisan('db:wipe', ['--drop-views' => true]);
        }

        $options = [
            '--path' => realpath(__DIR__.'/stubs/'),
            '--realpath' => true,
        ];

        $this->artisan('migrate', $options);

        $this->beforeApplicationDestroyed(function () use ($options) {
            $this->artisan('migrate:rollback', $options);
        });
    }

    public function testRealpathMigrationHasProperlyExecuted()
    {
        $this->assertTrue(Schema::hasTable('members'));
    }

    public function testMigrationsHasTheMigratedTable()
    {
        $this->assertDatabaseHas('migrations', [
            'id' => 1,
            'migration' => '2014_10_12_000000_create_members_table',
            'batch' => 1,
        ]);
    }
}
