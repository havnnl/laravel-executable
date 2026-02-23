<?php

declare(strict_types=1);

namespace Havn\Executable\Testing;

use Closure;
use DateTimeInterface;
use Havn\Executable\Jobs\ExecutableJob;
use Havn\Executable\Testing\Exceptions\ExecutionAssertionsNotAvailable;
use Havn\Executable\Testing\Facades\Execution;
use Havn\Executable\Testing\Queueing\PushedJob;
use Havn\Executable\Testing\Queueing\QueuedAssertion;
use Havn\Executable\Testing\Spying\ExecutableSpy;
use Havn\Executable\Testing\Spying\ShouldExecuteSpyExpectation;
use Havn\Executable\Testing\Spying\ShouldNeverExecuteSpyExpectation;
use Illuminate\Queue\Jobs\FakeJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Support\Testing\Fakes\QueueFake;
use PHPUnit\Framework\Assert;

final class ExecutionAssertions
{
    use InteractsWithTime;

    private string $executableBaseClass;

    /**
     * @param  class-string  $executableClass
     */
    private function __construct(private string $executableClass)
    {
        if (! App::runningUnitTests()) {
            throw new ExecutionAssertionsNotAvailable($executableClass);
        }

        $this->executableBaseClass = class_basename($executableClass);
    }

    /**
     * @param  class-string  $class
     */
    public static function for(string $class): self
    {
        return new self($class);
    }

    public function dump(): void
    {
        dump($this->queuedJobsForExecutable()->map(fn (PushedJob $job) => $job->summary())->toArray());
    }

    // Spying

    public function executed(): ShouldExecuteSpyExpectation
    {
        return $this->spy()->shouldExecute();
    }

    public function notExecuted(): ShouldNeverExecuteSpyExpectation
    {
        return $this->spy()->shouldNeverExecute();
    }

    // Queue Interactions

    public function released(DateTimeInterface|int|null $delay = null): void
    {
        Assert::assertTrue(
            $this->fakeJob()->isReleased(),
            "[$this->executableBaseClass] was expected to be released, but was not."
        );

        if (is_null($delay)) {
            return;
        }

        $delay = $delay instanceof DateTimeInterface
            ? $this->secondsUntil($delay)
            : $delay;

        Assert::assertEquals(
            $delay,
            $actualDelay = $this->fakeJob()->releaseDelay,
            "Expected [$this->executableBaseClass] to be released with delay of [$delay] seconds, but was released with delay of [$actualDelay] seconds."
        );
    }

    public function notReleased(): void
    {
        Assert::assertFalse(
            $this->fakeJob()->isReleased(),
            "[$this->executableBaseClass] was released unexpectedly."
        );
    }

    public function deleted(): void
    {
        Assert::assertTrue(
            $this->fakeJob()->isDeleted(),
            "[$this->executableBaseClass] was expected to be deleted, but was not."
        );
    }

    public function notDeleted(): void
    {
        Assert::assertFalse(
            $this->fakeJob()->isDeleted(),
            "[$this->executableBaseClass] was deleted unexpectedly."
        );
    }

    public function failed(): void
    {
        Assert::assertTrue(
            $this->fakeJob()->hasFailed(),
            "[$this->executableBaseClass] was expected to be manually failed, but was not."
        );
    }

    public function notFailed(): void
    {
        Assert::assertFalse(
            $this->fakeJob()->hasFailed(),
            "[$this->executableBaseClass] was manually failed unexpectedly."
        );
    }

    /**
     * @param  array<class-string|Closure>  $chain
     */
    public function hasChain(array $chain): void
    {
        Assert::assertTrue(
            PushedJob::from($this->testedExecutable())->hasChain($chain),
            "[$this->executableBaseClass] does not have the expected chain."
        );
    }

    public function hasNoChain(): void
    {
        Assert::assertTrue(
            PushedJob::from($this->testedExecutable())->hasNoChain(),
            "[$this->executableBaseClass] does have a chain unexpectedly."
        );
    }

    // Dispatching

    public function queued(): QueuedAssertion
    {
        return new QueuedAssertion($this->queuedJobsForExecutable(), $this->executableClass);
    }

    public function notQueued(): void
    {
        $this->queued()->never();
    }

    /**
     * @return Collection<int, PushedJob>
     */
    private function queuedJobsForExecutable(): Collection
    {
        $queue = Queue::getFacadeRoot();
        Assert::assertTrue($queue instanceof QueueFake, 'Queue was not faked. Use [Queue::fake()].');

        /** @var QueueFake $queue */
        return collect($queue->pushedJobs())
            ->flatten(1)
            ->pluck('job')
            ->filter(fn ($job) => $job instanceof ExecutableJob && $job->executableClass() == $this->executableClass)
            ->map(fn (ExecutableJob $job) => PushedJob::from($job));
    }

    private function spy(): ExecutableSpy
    {
        $instance = resolve($this->executableClass);

        Assert::assertTrue(
            $instance instanceof ExecutableSpy,
            "Spy not active for [$this->executableClass]. Use [$this->executableClass::spy()]"
        );

        return $instance;
    }

    private function testedExecutable(): ExecutableJob
    {
        $testing = Execution::getTestingJob();

        Assert::assertTrue(
            ! is_null($testing),
            "Testing not active for [$this->executableBaseClass]. Use [$this->executableBaseClass::test()]"
        );

        return $testing;
    }

    private function fakeJob(): FakeJob
    {
        $job = $this->testedExecutable()->job;

        Assert::assertInstanceOf(
            FakeJob::class,
            $job,
            'Expected FakeJob but got '.($job ? get_class($job) : 'null')
        );

        return $job;
    }
}
