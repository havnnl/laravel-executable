<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\QueueableExecutable;

class VariadicWithNamedLifecycleExecutable
{
    use QueueableExecutable;

    public function execute(string $name, string ...$input): string
    {
        return $name.':'.implode(',', $input);
    }

    public function displayName(string $name): string
    {
        $_SERVER['_variadic_named_lifecycle_name'] = $name;

        return 'display-'.$name;
    }
}
