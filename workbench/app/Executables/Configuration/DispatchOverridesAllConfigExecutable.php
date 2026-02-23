<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Closure;
use DateTimeInterface;
use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\QueueableExecutable;

class DispatchOverridesAllConfigExecutable
{
    use QueueableExecutable;

    public function configure(QueueableConfig $config, Closure $configCallback): void
    {
        $configCallback($config);
    }

    public function execute(Closure $configCallback): void {}

    public function backoff(): int
    {
        return 1;
    }

    public function displayName(): string
    {
        return 'method-display-name';
    }

    public function retryUntil(): ?DateTimeInterface
    {
        return now();
    }

    public function tries(): int
    {
        return 1;
    }

    public function uniqueFor(): int
    {
        return 1;
    }

    public function uniqueId(): int
    {
        return 1;
    }
}
