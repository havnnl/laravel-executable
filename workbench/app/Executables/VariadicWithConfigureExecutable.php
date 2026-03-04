<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\QueueableExecutable;

class VariadicWithConfigureExecutable
{
    use QueueableExecutable;

    public function execute(string $name, string ...$input): string
    {
        return $name.':'.implode(',', $input);
    }

    public function configure(QueueableConfig $config, string $name): void
    {
        $_SERVER['_variadic_configure_name'] = $name;
        $config->queue = 'custom-'.$name;
    }
}
