<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Exception;
use Havn\Executable\QueueableExecutable;

class VariadicWithVariadicFailedExecutable
{
    use QueueableExecutable;

    public function execute(string $name, string ...$input): string
    {
        throw new Exception('Variadic failure');
    }

    public function failed(Exception $exception, string ...$input): void
    {
        $_SERVER['_variadic_variadic_failed_input'] = $input;
        $_SERVER['_variadic_variadic_failed_exception'] = $exception;
    }
}
