<?php

declare(strict_types=1);

namespace Havn\Executable\Config;

use DateInterval;
use DateTimeInterface;
use UnitEnum;

final class QueueableConfig
{
    /**
     * @param  array<int, int>|int|null  $backoff
     * @param  array<int, object>  $chain
     * @param  array<int, int>|DateInterval|DateTimeInterface|int|null  $delay
     */
    public function __construct(
        public ?bool $afterCommit = null,
        public array|int|null $backoff = null,
        public array $chain = [],
        public string|UnitEnum|null $chainConnection = null,
        public string|UnitEnum|null $chainQueue = null,
        public string|UnitEnum|null $connection = null,
        public array|DateInterval|DateTimeInterface|int|null $delay = null,
        public ?bool $deleteWhenMissingModels = null,
        public ?string $displayName = null,
        public ?bool $failOnTimeout = null,
        public ?int $maxExceptions = null,
        public string|UnitEnum|null $queue = null,
        public DateTimeInterface|int|null $retryUntil = null,
        public ?bool $shouldBeEncrypted = null,
        public ?bool $shouldBeUnique = null,
        public ?bool $shouldBeUniqueUntilProcessing = null,
        public ?int $timeout = null,
        public ?int $tries = null,
        public ?int $uniqueFor = null,
        public int|string|null $uniqueId = null,
        public ?bool $withoutRelations = null,
    ) {}

    public function afterCommit(bool $afterCommit = true): QueueableConfig
    {
        $this->afterCommit = $afterCommit;

        return $this;
    }

    public function allOnConnection(string|UnitEnum|null $connection): QueueableConfig
    {
        $this->connection = $connection;
        $this->chainConnection = $connection;

        return $this;
    }

    public function allOnQueue(string|UnitEnum|null $queue): QueueableConfig
    {
        $this->queue = $queue;
        $this->chainQueue = $queue;

        return $this;
    }

    /**
     * @param  array<int, int>|int|null  $backoff
     */
    public function backoff(array|int|null $backoff): QueueableConfig
    {
        $this->backoff = $backoff;

        return $this;
    }

    public function beforeCommit(): QueueableConfig
    {
        $this->afterCommit = false;

        return $this;
    }

    /**
     * @param  array<int, object>  $chain
     */
    public function chain(array $chain): QueueableConfig
    {
        $this->chain = $chain;

        return $this;
    }

    /**
     * @param  array<int, int>|DateInterval|DateTimeInterface|int|null  $delay
     */
    public function delay(array|DateInterval|DateTimeInterface|int|null $delay): QueueableConfig
    {
        $this->delay = $delay;

        return $this;
    }

    public function deleteWhenMissingModels(bool $deleteWhenMissingModels = true): QueueableConfig
    {
        $this->deleteWhenMissingModels = $deleteWhenMissingModels;

        return $this;
    }

    public function displayName(?string $displayName): QueueableConfig
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function shouldBeEncrypted(bool $encrypted = true): QueueableConfig
    {
        $this->shouldBeEncrypted = $encrypted;

        return $this;
    }

    public function failOnTimeout(bool $failOnTimeout = true): QueueableConfig
    {
        $this->failOnTimeout = $failOnTimeout;

        return $this;
    }

    public function maxExceptions(?int $maxExceptions): QueueableConfig
    {
        $this->maxExceptions = $maxExceptions;

        return $this;
    }

    public function onConnection(string|UnitEnum|null $connection): QueueableConfig
    {
        $this->connection = $connection;

        return $this;
    }

    public function onQueue(string|UnitEnum|null $queue): QueueableConfig
    {
        $this->queue = $queue;

        return $this;
    }

    public function retryUntil(?DateTimeInterface $retryUntil): QueueableConfig
    {
        $this->retryUntil = $retryUntil;

        return $this;
    }

    public function timeout(?int $seconds): QueueableConfig
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function tries(?int $tries): QueueableConfig
    {
        $this->tries = $tries;

        return $this;
    }

    public function shouldBeUnique(): QueueableConfig
    {
        $this->shouldBeUnique = true;
        $this->shouldBeUniqueUntilProcessing = null;

        return $this;
    }

    public function uniqueId(int|string|null $uniqueId): QueueableConfig
    {
        $this->uniqueId = $uniqueId;

        return $this;
    }

    public function uniqueFor(?int $seconds): QueueableConfig
    {
        $this->uniqueFor = $seconds;

        return $this;
    }

    public function shouldBeUniqueUntilProcessing(): QueueableConfig
    {
        $this->shouldBeUnique = null;
        $this->shouldBeUniqueUntilProcessing = true;

        return $this;
    }

    public function withoutDelay(): QueueableConfig
    {
        $this->delay = null;

        return $this;
    }

    public function withoutRelations(): QueueableConfig
    {
        $this->withoutRelations = true;

        return $this;
    }
}
