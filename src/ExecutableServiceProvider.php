<?php

declare(strict_types=1);

namespace Havn\Executable;

use Havn\Executable\Testing\ExecutionManager;
use Illuminate\Support\ServiceProvider;

class ExecutableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/executable.php', 'executable');

        $this->app->singleton(ExecutionManager::class);
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes(
            [__DIR__.'/../config/executable.php' => config_path('executable.php')],
            'laravel-executable-config'
        );
    }
}
