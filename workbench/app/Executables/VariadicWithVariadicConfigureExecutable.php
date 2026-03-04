<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\QueueableExecutable;

class VariadicWithVariadicConfigureExecutable
{
    use QueueableExecutable;

    public function execute(string $name, string ...$input): string
    {
        return $name.':'.implode(',', $input);
    }

    public function configure(QueueableConfig $config, string ...$input): void
    {
        $_SERVER['_variadic_variadic_configure_input'] = $input;
        $config->queue = 'variadic-'.implode('-', $input);
    }
}
