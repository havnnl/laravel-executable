<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Havn\Executable\QueueableExecutable;
use Workbench\App\Middleware\FlagServerVariableMiddleware;
use Workbench\App\Middleware\PushToServerVariableMiddleware;

class FullyConfiguredByPropertiesExecutable
{
    use QueueableExecutable;

    public bool $afterCommit = true;

    public bool $deleteWhenMissingModels = true;

    public bool $failOnTimeout = true;

    public int $backoff = 5;

    public int $delay = 60;

    public int $maxExceptions = 3;

    public int $retryUntil = 1767225599;

    public bool $shouldBeEncrypted = true;

    public int $timeout = 120;

    public int $tries = 10;

    public int $uniqueFor = 90;

    public int $uniqueId = 100;

    public string $connection = 'property-connection';

    public string $queue = 'property-queue';

    /** @var array<int, class-string> */
    public array $middleware = [
        FlagServerVariableMiddleware::class,
        PushToServerVariableMiddleware::class,
    ];

    public function execute(mixed ...$args): void
    {
        // ..
    }
}
