<?php

declare(strict_types=1);

namespace Havn\Executable\Contracts;

use Closure;
use DateInterval;
use DateTimeInterface;
use Havn\Executable\ExecutionMode;
use Havn\Executable\PendingExecution;
use UnitEnum;

/**
 * @see ExecutionMode::QUEUE
 *
 * @internal
 *
 * @phpstan-method PendingExecution execute(...$args)
 */
interface QueuedExecutableMethods
{
    public function afterCommit(bool $afterCommit = true): static;

    public function allOnConnection(string|UnitEnum|null $connection): static;

    public function allOnQueue(string|UnitEnum|null $queue): static;

    public function beforeCommit(): static;

    /**
     * @param  array<int, object>  $jobs
     */
    public function chain(array $jobs): static;

    public function delay(DateInterval|DateTimeInterface|int|null $delay): static;

    public function deleteWhenMissingModels(bool $deleteWhenMissingModels = true): static;

    public function failOnTimeout(bool $failOnTimeout = true): static;

    public function maxExceptions(?int $maxExceptions): static;

    public function onConnection(string|UnitEnum|null $connection): static;

    public function onQueue(string|UnitEnum|null $queue): static;

    public function shouldBeEncrypted(bool $encrypted = true): static;

    public function shouldBeUnique(): static;

    public function shouldBeUniqueUntilProcessing(): static;

    public function shouldRetryUntil(?DateTimeInterface $retryUntil): static;

    public function timeout(?int $seconds): static;

    public function unless(bool|Closure $condition): static;

    public function when(bool|Closure $condition): static;

    /**
     * @param  array<int, int>|int|null  $backoff
     */
    public function withBackoff(array|int|null $backoff): static;

    public function withDisplayName(?string $displayName): static;

    public function withTries(?int $tries): static;

    public function withUniqueFor(?int $seconds): static;

    public function withUniqueId(int|string|null $uniqueId): static;

    public function withoutDelay(): static;
}
