<?php

declare(strict_types=1);

namespace Havn\Executable\Contracts;

use Closure;
use Havn\Executable\ExecutionMode;
use Havn\Executable\PendingExecution;

/**
 * @see ExecutionMode::SYNC
 *
 * @internal
 *
 * @phpstan-method PendingExecution execute(...$args)
 */
interface SyncExecutableMethods
{
    public function afterResponse(): static;

    public function when(bool|Closure $condition): static;

    public function unless(bool|Closure $condition): static;
}
