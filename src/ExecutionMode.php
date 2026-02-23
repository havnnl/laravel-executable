<?php

declare(strict_types=1);

namespace Havn\Executable;

use Havn\Executable\Contracts\PreparedExecutableMethods;
use Havn\Executable\Contracts\QueuedExecutableMethods;
use Havn\Executable\Contracts\SyncExecutableMethods;
use Havn\Executable\Contracts\TestedExecutableMethods;

/**
 * @internal
 */
enum ExecutionMode
{
    case SYNC;
    case QUEUE;
    case PREPARE;
    case TEST;

    public function hasInvocationMethod(string $method): bool
    {
        return method_exists($this->invocationMethodContract(), $method);
    }

    private function invocationMethodContract(): string
    {
        return match ($this) {
            self::SYNC => SyncExecutableMethods::class,
            self::QUEUE => QueuedExecutableMethods::class,
            self::PREPARE => PreparedExecutableMethods::class,
            self::TEST => TestedExecutableMethods::class,
        };
    }
}
