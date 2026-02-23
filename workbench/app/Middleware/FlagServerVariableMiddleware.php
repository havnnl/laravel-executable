<?php

declare(strict_types=1);

namespace Workbench\App\Middleware;

use Closure;
use Havn\Executable\Jobs\ExecutableJob;

class FlagServerVariableMiddleware
{
    public function handle(ExecutableJob $job, Closure $next): void
    {
        $_SERVER['_middleware_flag'] = true;

        $next($job);
    }
}
