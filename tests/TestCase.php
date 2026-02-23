<?php

declare(strict_types=1);

namespace Havn\Executable\Tests;

use Havn\Executable\ExecutableServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ExecutableServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/../workbench/database/migrations/0000_00_00_000000_create_test_tables.php';
        $migration->up();
    }
}
