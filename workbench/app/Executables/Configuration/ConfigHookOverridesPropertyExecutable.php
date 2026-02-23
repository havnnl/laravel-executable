<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Closure;
use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\QueueableExecutable;

class ConfigHookOverridesPropertyExecutable
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

    public function configure(QueueableConfig $config, Closure $configCallback): void
    {
        $configCallback($config);
    }

    public function execute(Closure $configCallback): void
    {
        // ..
    }
}
