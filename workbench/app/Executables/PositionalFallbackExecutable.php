<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\QueueableExecutable;

class PositionalFallbackExecutable
{
    use QueueableExecutable;

    public function execute(string $input): string
    {
        return $input;
    }

    /**
     * Parameter name intentionally differs from execute()'s $input
     * to exercise the BindingResolutionException positional fallback.
     */
    public function displayName(string $value): string
    {
        return 'display:'.$value;
    }
}
