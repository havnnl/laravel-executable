<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Havn\Executable\QueueableExecutable;
use Workbench\App\Middleware\FlagServerVariableMiddleware;
use Workbench\App\Middleware\PushToServerVariableMiddleware;

class MiddlewareMergingExecutable
{
    use QueueableExecutable;

    /** @var array<int, class-string> */
    public array $middleware = [
        FlagServerVariableMiddleware::class,
    ];

    public function execute(): void
    {
        // ..
    }

    /**
     * @return array<int, mixed>
     */
    public function middleware(): array
    {
        return [
            PushToServerVariableMiddleware::class,
        ];
    }
}
