<?php

declare(strict_types=1);

namespace Havn\Executable;

use Havn\Executable\Contracts\PreparedExecutableMethods;
use Havn\Executable\Contracts\QueuedExecutableMethods;
use Havn\Executable\Jobs\ExecutableJob;
use Illuminate\Bus\Batch;
use Throwable;

/**
 * @method static static|QueuedExecutableMethods onQueue(string $queue = null)
 * @method static static|PreparedExecutableMethods prepare()
 */
trait QueueableExecutable
{
    use Executable {
        __call as __parentCall;
        __callStatic as __parentCallStatic;
    }

    protected ?ExecutableJob $executableJob = null;

    protected function attempts(): int
    {
        return $this->executableJob ? $this->executableJob->attempts() : 1;
    }

    protected function delete(): void
    {
        $this->executableJob?->delete();
    }

    protected function fail(string|Throwable|null $exception = null): void
    {
        $this->executableJob?->fail($exception);
    }

    protected function release(int $delay = 0): void
    {
        $this->executableJob?->release($delay);
    }

    protected function prependToChain(mixed $job): static
    {
        $this->executableJob?->prependToChain($job);

        return $this;
    }

    protected function appendToChain(mixed $job): static
    {
        $this->executableJob?->appendToChain($job);

        return $this;
    }

    protected function batch(): ?Batch
    {
        return $this->executableJob?->batch();
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return match ($name) {
            'onQueue' => PendingExecution::queueFor(static::class, $arguments[0] ?? null),
            'prepare' => PendingExecution::prepareFor(static::class),
            default => static::__parentCallStatic($name, $arguments),
        };
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return match ($name) {
            'onQueue' => PendingExecution::queueFor($this, $arguments[0] ?? null),
            'prepare' => PendingExecution::prepareFor($this),
            default => static::__parentCall($name, $arguments),
        };
    }
}
