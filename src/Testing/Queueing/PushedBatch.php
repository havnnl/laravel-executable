<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Queueing;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Testing\Fakes\PendingBatchFake;
use PHPUnit\Framework\Assert;

final readonly class PushedBatch
{
    public function __construct(private PendingBatchFake $batch) {}

    public function isOnConnection(string $connection): bool
    {
        return $this->batch->connection() === $connection;
    }

    public function assertIsOnConnection(string $connection): self
    {
        Assert::assertTrue(
            $this->isOnConnection($connection),
            sprintf(
                'Batch is on connection [%s] instead of [%s]',
                $this->batch->connection(),
                $connection
            )
        );

        return $this;
    }

    public function isOnQueue(string $queue): bool
    {
        return $this->batch->queue() === $queue;
    }

    public function assertIsOnQueue(string $queue): self
    {
        Assert::assertTrue(
            $this->isOnQueue($queue),
            sprintf(
                'Batch is on queue [%s] instead of [%s]',
                $this->batch->queue(),
                $queue
            )
        );

        return $this;
    }

    public function allowsFailures(bool $allowFailures = true): bool
    {
        return $this->batch->allowsFailures() === $allowFailures;
    }

    public function assertAllowsFailures(bool $allowFailures = true): self
    {
        Assert::assertTrue(
            $this->allowsFailures($allowFailures),
            $allowFailures
                ? 'Batch does not allow failures'
                : 'Batch allows failures'
        );

        return $this;
    }

    public function hasName(string $name): bool
    {
        return $this->batch->name === $name;
    }

    public function assertHasName(string $name): self
    {
        Assert::assertTrue(
            $this->hasName($name),
            sprintf(
                'Batch has name [%s] instead of [%s]',
                $this->batch->name,
                $name
            )
        );

        return $this;
    }

    public function containsProgressCallback(?callable $callback = null): bool
    {
        return collect($this->batch->progressCallbacks())
            ->filter($callback ?: fn () => true)
            ->isNotEmpty();
    }

    public function assertContainsProgressCallback(?callable $callback = null): self
    {
        Assert::assertTrue(
            $this->containsProgressCallback($callback),
            'Batch does not contain expected progress callback'
        );

        return $this;
    }

    public function containsBeforeCallback(?callable $callback = null): bool
    {
        return collect($this->batch->beforeCallbacks())
            ->filter($callback ?: fn () => true)
            ->isNotEmpty();
    }

    public function assertContainsBeforeCallback(?callable $callback = null): self
    {
        Assert::assertTrue(
            $this->containsBeforeCallback($callback),
            'Batch does not contain expected before callback'
        );

        return $this;
    }

    public function containsThenCallback(?callable $callback = null): bool
    {
        return collect($this->batch->thenCallbacks())
            ->filter($callback ?: fn () => true)
            ->isNotEmpty();
    }

    public function assertContainsThenCallback(?callable $callback = null): self
    {
        Assert::assertTrue(
            $this->containsThenCallback($callback),
            'Batch does not contain expected then callback'
        );

        return $this;
    }

    public function containsCatchCallback(?callable $callback = null): bool
    {
        return collect($this->batch->catchCallbacks())
            ->filter($callback ?: fn () => true)
            ->isNotEmpty();
    }

    public function assertContainsCatchCallback(?callable $callback = null): self
    {
        Assert::assertTrue(
            $this->containsCatchCallback($callback),
            'Batch does not contain expected catch callback'
        );

        return $this;
    }

    public function containsFinallyCallback(?callable $callback = null): bool
    {
        return collect($this->batch->finallyCallbacks())
            ->filter($callback ?: fn () => true)
            ->isNotEmpty();
    }

    public function assertContainsFinallyCallback(?callable $callback = null): self
    {
        Assert::assertTrue(
            $this->containsFinallyCallback($callback),
            'Batch does not contain expected finally callback'
        );

        return $this;
    }

    public function hasOption(string $key, ?callable $value = null): bool
    {
        if (! array_key_exists($key, $this->batch->options)) {
            return false;
        }

        return is_null($value) || $value($this->batch->options[$key]);
    }

    public function assertHasOption(string $key, ?callable $value = null): self
    {
        Assert::assertTrue(
            $this->hasOption($key, $value),
            sprintf('Batch does not have expected option: [%s]', $key)
        );

        return $this;
    }

    public function hasCount(int $count): bool
    {
        return $this->batch->jobs->count() == $count;
    }

    public function assertHasCount(int $count): self
    {
        Assert::assertTrue(
            $this->hasCount($count),
            sprintf(
                'Batch has [%d] jobs instead of [%d]',
                $this->batch->jobs->count(),
                $count
            )
        );

        return $this;
    }

    public function contains(Closure|string $callback): bool
    {
        return $this->jobs()->filter(function (PushedJob $job) use ($callback) {
            return $job->is($callback);
        })->isNotEmpty();
    }

    public function assertContains(Closure|string $callback): self
    {
        Assert::assertTrue(
            $this->contains($callback),
            'Batch does not contain expected job'
        );

        return $this;
    }

    /**
     * @param  array<class-string|Closure>  $expectedJobs
     */
    public function containsExactly(array $expectedJobs): bool
    {
        $batchedJobs = $this->jobs();

        foreach ($expectedJobs as $expectedJob) {
            $index = $batchedJobs->search(fn (PushedJob $job) => $job->is($expectedJob));

            if ($index === false) {
                return false;
            }

            $batchedJobs->pull($index);
        }

        return $batchedJobs->isEmpty();
    }

    /**
     * @param  array<class-string|Closure>  $expectedJobs
     */
    public function assertContainsExactly(array $expectedJobs): self
    {
        Assert::assertTrue(
            $this->containsExactly($expectedJobs),
            'Batch does not contain exactly the expected jobs'
        );

        return $this;
    }

    /**
     * @return Collection<int, PushedJob>
     */
    private function jobs(): Collection
    {
        return $this->batch->jobs->map(fn ($job) => PushedJob::from($job));
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
        return [
            'name' => $this->batch->name,
            'connection' => $this->batch->connection(),
            'queue' => $this->batch->queue(),
            'allowsFailures' => $this->batch->allowsFailures(),
            'jobs_count' => $this->batch->jobs->count(),
            'jobs' => $this->jobs()->map(fn (PushedJob $job) => $job->summary())->toArray(),
            'progress_callbacks_count' => count($this->batch->progressCallbacks()),
            'before_callbacks_count' => count($this->batch->beforeCallbacks()),
            'then_callbacks_count' => count($this->batch->thenCallbacks()),
            'catch_callbacks_count' => count($this->batch->catchCallbacks()),
            'finally_callbacks_count' => count($this->batch->finallyCallbacks()),
        ];
    }
}
