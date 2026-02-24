<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Queueing;

use Closure;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert;

final class QueuedAssertion
{
    private ?Closure $argumentFilter = null;

    private ?string $queueFilter = null;

    /** @var array<class-string|Closure>|null */
    private ?array $chainFilter = null;

    /** @var array<int, callable> */
    private array $customFilters = [];

    private ?int $expectedCount = null;

    private bool $verified = false;

    private string $executableClass;

    /**
     * @param  Collection<int, PushedJob>  $jobs
     */
    public function __construct(
        private readonly Collection $jobs,
        string $executableClass
    ) {
        $this->executableClass = class_basename($executableClass);
    }

    public function with(mixed ...$args): self
    {
        $this->argumentFilter = static fn (PushedJob $job) => $job->executedWith(...$args);

        return $this;
    }

    public function withArgs(callable $callback): self
    {
        $this->argumentFilter = static fn (PushedJob $job) => $job->executedWithArgs($callback);

        return $this;
    }

    public function onQueue(string $queue): self
    {
        $this->queueFilter = $queue;

        return $this;
    }

    /**
     * @param  array<class-string|Closure>  $chain
     */
    public function withChain(array $chain): self
    {
        $this->chainFilter = $chain;

        return $this;
    }

    public function where(callable $callback): self
    {
        $this->customFilters[] = $callback;

        return $this;
    }

    public function dump(): self
    {
        dump($this->applyFilters()->map(fn (PushedJob $job) => $job->summary())->toArray());

        return $this;
    }

    public function dd(): never
    {
        dd($this->applyFilters()->map(fn (PushedJob $job) => $job->summary())->toArray());
    }

    public function never(): self
    {
        return $this->times(0);
    }

    public function once(): self
    {
        return $this->times(1);
    }

    public function twice(): self
    {
        return $this->times(2);
    }

    public function times(int $count): self
    {
        $this->expectedCount = $count;

        return $this;
    }

    /**
     * @return Collection<int, PushedJob>
     */
    private function applyFilters(): Collection
    {
        return $this->jobs
            ->when($this->argumentFilter !== null, fn ($jobs) => $jobs->filter($this->argumentFilter))
            ->when($this->queueFilter !== null, fn ($jobs) => $jobs->filter(fn (PushedJob $job) => $job->isOnQueue($this->queueFilter)))
            ->when($this->chainFilter !== null, fn ($jobs) => $jobs->filter(fn (PushedJob $job) => $job->hasChain($this->chainFilter)))
            ->when(! empty($this->customFilters), fn ($jobs) => $jobs->filter(
                fn (PushedJob $job) => collect($this->customFilters)->every(fn (callable $filter) => $filter($job))
            ));
    }

    private function buildCountErrorMessage(int $expected, int $actual): string
    {
        $filterDescription = $this->buildFilterDescription();

        return "[$this->executableClass] was queued [$actual] times instead of [$expected] times{$filterDescription}.";
    }

    private function buildFilterDescription(): string
    {
        $filters = [];

        if ($this->argumentFilter !== null) {
            $filters[] = 'with specific arguments';
        }

        if ($this->queueFilter !== null) {
            $filters[] = "on queue [{$this->queueFilter}]";
        }

        if ($this->chainFilter !== null) {
            $filters[] = 'with chain';
        }

        if (! empty($this->customFilters)) {
            $filters[] = 'matching custom filters';
        }

        return empty($filters) ? '' : ' '.implode(' ', $filters);
    }

    public function __destruct()
    {
        if ($this->verified) {
            return;
        }

        $this->verified = true;

        $filtered = $this->applyFilters();
        $actualCount = $filtered->count();

        if ($this->expectedCount !== null) {
            Assert::assertSame(
                $this->expectedCount,
                $actualCount,
                $this->buildCountErrorMessage($this->expectedCount, $actualCount)
            );
        } else {
            Assert::assertTrue(
                $actualCount > 0,
                $this->buildNotQueuedErrorMessage()
            );
        }
    }

    private function buildNotQueuedErrorMessage(): string
    {
        return "[$this->executableClass] was not queued{$this->buildFilterDescription()}.";
    }
}
