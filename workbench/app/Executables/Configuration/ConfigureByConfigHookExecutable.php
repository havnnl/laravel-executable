<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Closure;
use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\QueueableExecutable;

class ConfigureByConfigHookExecutable
{
    use QueueableExecutable;

    public function configure(QueueableConfig $config, Closure $callback, mixed $input = null): void
    {
        $callback($config, $input);
    }

    public function execute(Closure $callback, mixed $input = null): void
    {
        // ..
    }
}
