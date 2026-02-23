<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Queueing;

use Closure;
use DateInterval;
use DateTimeInterface;
use Havn\Executable\Jobs\ExecutableJob;
use Havn\Executable\Testing\Exceptions\CannotCheckArgumentsForJob;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ReflectsClosures;
use PHPUnit\Framework\Assert;

final class PushedJob
{
    use ReflectsClosures;

    private string $jobClass;

    private function __construct(private readonly object $job)
    {
        $this->jobClass = class_basename($this->job);
    }

    public static function from(object $job): self
    {
        return new self($job);
    }

    public function job(): object
    {
        return $this->job;
    }

    public function is(Closure|string $callable): bool
    {
        if (is_string($callable)) {
            return $this->job instanceof ExecutableJob
                ? $this->job->executableClass() === $callable
                : get_class($this->job) === $callable;
        }

        $typeHint = $this->firstClosureParameterType($callable);

        return (bool) match (true) {
            $this->job instanceof $typeHint => $callable($this->job),
            $this->job instanceof ExecutableJob && $this->job->executable() instanceof $typeHint => $callable($this->job->executable()),
            is_a($typeHint, PushedJob::class, true) => $callable($this),
            default => false
        };
    }

    public function assertIs(Closure|string $callable): self
    {
        Assert::assertTrue($this->is($callable), "[$this->jobClass] is not expected job.");

        return $this;
    }

    public function isOnConnection(string $connection): bool
    {
        return $this->getProperty('connection') === $connection;
    }

    public function assertIsOnConnection(string $connection): self
    {
        Assert::assertTrue($this->isOnConnection($connection), sprintf(
            '[%s] is on connection [%s] instead of [%s]',
            $this->jobClass,
            $this->getProperty('connection'),
            $connection
        ));

        return $this;
    }

    public function isOnQueue(string $queue): bool
    {
        return $this->getProperty('queue') === $queue;
    }

    public function assertIsOnQueue(string $queue): self
    {
        Assert::assertTrue($this->isOnQueue($queue), sprintf(
            '[%s] is on queue [%s] instead of [%s]',
            $this->jobClass,
            $this->getProperty('queue'),
            $queue
        ));

        return $this;
    }

    public function isEncrypted(bool $encrypted = true): bool
    {
        return ($this->job instanceof ShouldBeEncrypted ||
                $this->getProperty('shouldBeEncrypted')) === $encrypted;
    }

    public function assertIsEncrypted(bool $encrypted = true): self
    {
        $encrypted
            ? Assert::assertTrue($this->isEncrypted($encrypted), "[$this->jobClass] is not encrypted.")
            : Assert::assertTrue($this->isEncrypted($encrypted), "[$this->jobClass] is encrypted.");

        return $this;
    }

    public function isUnique(int|string|null $uniqueId = null): bool
    {
        return $this->job instanceof ShouldBeUnique
            && (! $uniqueId || $this->hasUniqueId($uniqueId));
    }

    public function assertIsUnique(int|string|null $uniqueId = null): self
    {
        Assert::assertTrue($this->isUnique(), "[$this->jobClass] is not unique.");
        Assert::assertTrue($this->isUnique($uniqueId), "[$this->jobClass] is unique but with different uniqueId.");

        return $this;
    }

    public function isUniqueFor(int $seconds): bool
    {
        return $this->isUnique()
            && $seconds === $this->getMethodOrProperty('uniqueFor');
    }

    public function assertIsUniqueFor(int $seconds): self
    {
        $this->assertIsUnique();
        Assert::assertTrue($this->isUniqueFor($seconds), "[$this->jobClass] is unique but for different duration.");

        return $this;
    }

    public function isUniqueUntilProcessing(int|string|null $uniqueId = null): bool
    {
        return $this->job instanceof ShouldBeUniqueUntilProcessing
            && (! $uniqueId || $this->hasUniqueId($uniqueId));
    }

    public function assertIsUniqueUntilProcessing(int|string|null $uniqueId = null): self
    {
        Assert::assertTrue($this->isUniqueUntilProcessing(), "[$this->jobClass] is not unique until processing.");
        Assert::assertTrue($this->isUniqueUntilProcessing($uniqueId), "[$this->jobClass] is unique until processing but with different uniqueId.");

        return $this;
    }

    public function hasUniqueId(int|string|null $uniqueId = null): bool
    {
        $actualId = $this->getMethodOrProperty('uniqueId');

        if ($this->job instanceof ExecutableJob) {
            $actualId = str((string) $this->getMethodOrProperty('uniqueId'))
                ->after($this->job->executableClass())
                ->after(':')
                ->toString() ?: null;
        }

        return is_null($uniqueId)
            ? ! is_null($actualId)
            : $uniqueId === $actualId;
    }

    public function assertHasUniqueId(int|string|null $uniqueId = null): self
    {
        Assert::assertTrue($this->hasUniqueId(), "[$this->jobClass] does not have uniqueId.");
        Assert::assertTrue($this->hasUniqueId($uniqueId), "[$this->jobClass] has a different uniqueId than expected.");

        return $this;
    }

    public function isDelayed(DateInterval|DateTimeInterface|int|null $delay = null): bool
    {
        $jobDelay = $this->getProperty('delay');

        return is_null($delay)
            ? ! is_null($jobDelay)
            : $jobDelay === $delay;
    }

    public function assertIsDelayed(DateInterval|DateTimeInterface|int|null $delay = null): self
    {
        Assert::assertTrue($this->isDelayed(), "[$this->jobClass] is not delayed.");
        Assert::assertTrue($this->isDelayed($delay), "[$this->jobClass] is delayed but with a different duration.");

        return $this;
    }

    public function executedWith(mixed ...$expectedArguments): bool
    {
        if (! $this->job instanceof ExecutableJob) {
            throw new CannotCheckArgumentsForJob;
        }

        $actualArguments = array_values($this->job->arguments());

        if (count($expectedArguments) != count($actualArguments)) {
            return false;
        }

        foreach ($expectedArguments as $index => $value) {
            $actualArgument = $actualArguments[$index];

            if (! $this->argumentsAreSame($value, $actualArgument)) {
                return false;
            }
        }

        return true;
    }

    private function argumentsAreSame(mixed $arg1, mixed $arg2): bool
    {
        if ($arg1 instanceof Model && $arg2 instanceof Model) {
            return $arg1->is($arg2);
        }

        return $arg1 === $arg2;
    }

    public function assertExecutedWith(mixed ...$expectedArguments): self
    {
        Assert::assertTrue($this->executedWith(...$expectedArguments), "[$this->jobClass] is not executed with expected arguments.");

        return $this;
    }

    public function executedWithArgs(callable $callback): bool
    {
        if (! $this->job instanceof ExecutableJob) {
            throw new CannotCheckArgumentsForJob;
        }

        return $callback(...array_values($this->job->arguments()));
    }

    public function assertExecutedWithArgs(callable $callback): self
    {
        Assert::assertTrue($this->executedWithArgs($callback), "[$this->jobClass] is not executed with expected arguments.");

        return $this;
    }

    /**
     * @param  array<class-string|Closure>  $chain
     */
    public function hasChain(?array $chain = null): bool
    {
        $actualChain = $this->chain();

        if (is_null($chain)) {
            return $actualChain->isNotEmpty();
        } elseif (empty($chain)) {
            return $actualChain->isEmpty();
        }

        $expectedCount = count($chain);
        $actualCount = $actualChain->count();

        if ($actualCount != $expectedCount) {
            return false;
        }

        return collect($chain)
            ->reject(fn ($expectedJob, $index) => $actualChain[$index]->is($expectedJob))
            ->isEmpty();
    }

    /**
     * @param  array<class-string|Closure>|null  $chain
     */
    public function assertHasChain(?array $chain = null): self
    {
        Assert::assertTrue($this->hasChain(), "[$this->jobClass] does not have a chain.");
        Assert::assertTrue($this->hasChain($chain), "[$this->jobClass] has a chain but with different jobs.");

        return $this;
    }

    public function hasNoChain(): bool
    {
        return $this->hasChain([]);
    }

    public function assertHasNoChain(): self
    {
        Assert::assertTrue($this->hasChain([]), "[$this->jobClass] does have a chain.");

        return $this;
    }

    public function hasMiddleware(callable|string|null $middleware = null): bool
    {
        $middlewares = $this->getJobMiddleware();

        if ($middlewares->isEmpty()) {
            return false;
        }

        if (is_null($middleware)) {
            return true;
        }

        if (is_string($middleware)) {
            return $middlewares->contains($middleware);
        }

        return $middlewares->contains($middleware);
    }

    public function assertHasMiddleware(callable|string|null $middleware = null): self
    {
        Assert::assertTrue(
            $this->hasMiddleware($middleware),
            is_null($middleware)
                ? "[$this->jobClass] does not have any middleware."
                : "[$this->jobClass] does not have expected middleware."
        );

        return $this;
    }

    /**
     * @param  array<int, string>|null  $tags
     */
    public function hasTags(?array $tags = null): bool
    {
        /** @var array<int, string>|null $actualTags */
        $actualTags = $this->getMethod('tags');

        if (is_null($tags)) {
            return ! empty($actualTags);
        }

        return collect($tags)->diff($actualTags ?? [])->isEmpty()
            && collect($actualTags ?? [])->diff($tags)->isEmpty();
    }

    /**
     * @param  array<int, string>|null  $tags
     */
    public function assertHasTags(?array $tags = null): self
    {
        if (is_null($tags)) {
            Assert::assertTrue(
                $this->hasTags(),
                "[$this->jobClass] does not have any tags."
            );
        } else {
            /** @var array<int, string> $actualTags */
            $actualTags = $this->getMethod('tags') ?? [];

            Assert::assertEquals(
                collect($tags)->sort()->values()->toArray(),
                collect($actualTags)->sort()->values()->toArray(),
                "[$this->jobClass] does not have expected tags."
            );
        }

        return $this;
    }

    public function dump(): self
    {
        dump($this->summary());

        return $this;
    }

    public function dd(): never
    {
        dd($this->summary());
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        if ($this->job instanceof ExecutableJob) {
            $summary = [
                'executable' => $this->job->executableClass(),
                'arguments' => collect($this->job->arguments())
                    ->map(fn ($argument) => is_object($argument) ? get_class($argument) : $argument)
                    ->toArray(),
            ];
        } else {
            $summary = [
                'job' => get_class($this->job),
            ];
        }

        $summary['chain'] = $this->chain()->map(fn (PushedJob $chainedJob) => $chainedJob->summary())->toArray();

        return $summary;
    }

    /**
     * @return Collection<int, PushedJob>
     */
    private function chain(): Collection
    {
        /** @var array<int, string> $chained */
        $chained = $this->getProperty('chained') ?? [];

        return collect($chained)
            ->map(fn ($chainedJob) => PushedJob::from(unserialize($chainedJob)));
    }

    /**
     * Get job middleware merged in Laravel's order: method first, then property
     *
     * @return Collection<int, mixed>
     */
    private function getJobMiddleware(): Collection
    {
        /** @var array<int, mixed> $middleware */
        $middleware = array_merge(
            $this->getMethod('middleware') ?? [],
            $this->getProperty('middleware') ?? []
        );

        return collect($middleware);
    }

    private function getProperty(string $property): mixed
    {
        return $this->job->{$property} ?? null;
    }

    private function getMethod(string $method): mixed
    {
        return method_exists($this->job, $method)
            ? $this->job->$method()
            : null;
    }

    private function getMethodOrProperty(string $method): mixed
    {
        return method_exists($this->job, $method)
            ? $this->job->$method()
            : $this->getProperty($method);
    }
}
