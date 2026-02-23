<?php

declare(strict_types=1);

namespace Workbench\App\Executables\DynamicInjection;

use Havn\Executable\QueueableExecutable;
use Throwable;

class ThrowableNameCollisionExecutable
{
    use QueueableExecutable;

    public function execute(string $throwable, int $amount): void
    {
        throw new \RuntimeException('Execution failed');
    }

    public function failed(Throwable $throwable, int $amount): void
    {
        $_SERVER['_throwable_collision_throwable'] = $throwable;
        $_SERVER['_throwable_collision_amount'] = $amount;
    }
}
