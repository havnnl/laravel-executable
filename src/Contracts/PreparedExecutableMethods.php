<?php

declare(strict_types=1);

namespace Havn\Executable\Contracts;

use DateInterval;
use DateTimeInterface;
use Havn\Executable\ExecutionMode;
use Havn\Executable\Jobs\ExecutableJob;
use UnitEnum;

/**
 * @see ExecutionMode::PREPARE
 *
 * @internal
 *
 * @phpstan-method ExecutableJob execute(...$args)
 */
interface PreparedExecutableMethods
{
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

    public function shouldRetryUntil(?DateTimeInterface $retryUntil): static;

    public function timeout(?int $seconds): static;

    /**
     * @param  array<int, int>|int|null  $backoff
     */
    public function withBackoff(array|int|null $backoff): static;

    public function withDisplayName(?string $displayName): static;

    public function withTries(?int $tries): static;

    public function withoutDelay(): static;
}
