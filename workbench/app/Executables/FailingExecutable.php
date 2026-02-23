<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Exception;
use Havn\Executable\QueueableExecutable;

class FailingExecutable
{
    use QueueableExecutable;

    public function execute(string $input): mixed
    {
        throw new Exception('I failed');
    }

    public function failed(Exception $exception, string $input): void
    {
        $_SERVER['_failing_executable_exception'] = $exception;
        $_SERVER['_failing_executable_arguments'] = [$input];
    }
}
