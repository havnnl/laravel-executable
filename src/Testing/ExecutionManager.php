<?php

declare(strict_types=1);

namespace Havn\Executable\Testing;

use Closure;
use Havn\Executable\Jobs\ExecutableJob;
use Havn\Executable\Testing\Exceptions\CannotTestMultipleExecutables;
use Havn\Executable\Testing\Exceptions\ExecutionNotAvailable;
use Havn\Executable\Testing\Queueing\PushedBatch;
use Havn\Executable\Testing\Queueing\PushedJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Testing\Fakes\BusFake;
use Illuminate\Support\Testing\Fakes\PendingBatchFake;
use Illuminate\Support\Testing\Fakes\QueueFake;
use PHPUnit\Framework\Assert;

final class ExecutionManager
{
    private ?ExecutableJob $testing = null;

    public function __construct()
    {
        if (! App::runningUnitTests()) {
            throw new ExecutionNotAvailable;
        }
    }

    public function setTestingJob(ExecutableJob $job): void
    {
        if ($this->testing) {
            throw new CannotTestMultipleExecutables(class_basename($this->testing->executableClass()));
        }

        $this->testing = $job;
    }

    public function getTestingJob(): ?ExecutableJob
    {
        return $this->testing;
    }

    /**
     * @param  array<class-string|Closure>|callable  $callback
     */
    public function assertBatched(array|callable $callback): void
    {
        $this->assertBusIsFake();

        Bus::assertBatched(function ($batch) use ($callback) {

            $pushedBatch = new PushedBatch($batch);

            return is_array($callback)
                ? $pushedBatch->containsExactly($callback)
                : $callback($pushedBatch);
        });
    }

    public function assertBatchCount(int $count): void
    {
        $this->assertBusIsFake();

        $actualCount = $this->pushedBatches()->count();

        Assert::assertTrue(
            $count == $actualCount,
            "A batch was pushed [$actualCount] times instead of [$count] times.");
    }

    public function assertNothingBatched(): void
    {
        $this->assertBusIsFake();

        Bus::assertNothingBatched();
    }

    /**
     * @return Collection<int, PushedBatch>
     */
    public function batched(?callable $filter = null): Collection
    {
        $this->assertBusIsFake();

        $batches = $this->pushedBatches();

        return $filter ? $batches->filter($filter) : $batches;
    }

    public function dumpJobs(): void
    {
        $this->assertQueueIsFake();

        $this->pushedJobs()
            ->map(fn (PushedJob $job) => $job->summary())
            ->dump();
    }

    public function dumpBatches(): void
    {
        $this->assertBusIsFake();

        $this->pushedBatches()
            ->map(fn (PushedBatch $batch) => $batch->summary())
            ->dump();
    }

    /**
     * @return Collection<int, PushedBatch>
     */
    private function pushedBatches(): Collection
    {
        return collect(Bus::batched(fn () => true))
            ->map(fn (PendingBatchFake $batch) => new PushedBatch($batch));
    }

    /**
     * @return Collection<int, PushedJob>
     */
    private function pushedJobs(): Collection
    {
        /** @var QueueFake $queue */
        $queue = Queue::getFacadeRoot();

        return collect($queue->pushedJobs())
            ->flatten(1)
            ->pluck('job')
            ->map(fn (object $job) => PushedJob::from($job));
    }

    private function assertQueueIsFake(): void
    {
        Assert::assertTrue(Queue::getFacadeRoot() instanceof QueueFake, 'Queue was not faked. Use [Queue::fake()].');
    }

    private function assertBusIsFake(): void
    {
        Assert::assertTrue(Bus::getFacadeRoot() instanceof BusFake, 'Bus was not faked. Use [Bus::fake()].');
    }
}
