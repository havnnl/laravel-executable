<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Exception;
use Havn\Executable\QueueableExecutable;

class VariadicWithFailedExecutable
{
    use QueueableExecutable;

    public function execute(string $name, string ...$input): string
    {
        throw new Exception('Variadic failure');
    }

    public function failed(Exception $exception, string $name): void
    {
        $_SERVER['_variadic_failed_name'] = $name;
        $_SERVER['_variadic_failed_exception'] = $exception;
    }
}
