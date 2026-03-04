<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\QueueableExecutable;

class PurelyVariadicWithLifecycleExecutable
{
    use QueueableExecutable;

    public function execute(string ...$input): string
    {
        return implode(',', $input);
    }

    public function displayName(): string
    {
        return 'display-purely-variadic';
    }
}
