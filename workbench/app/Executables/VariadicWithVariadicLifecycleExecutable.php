<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\QueueableExecutable;

class VariadicWithVariadicLifecycleExecutable
{
    use QueueableExecutable;

    public function execute(string $name, string ...$input): string
    {
        return $name.':'.implode(',', $input);
    }

    public function displayName(string ...$input): string
    {
        $_SERVER['_variadic_variadic_lifecycle_input'] = $input;

        return 'display-'.implode('-', $input);
    }
}
