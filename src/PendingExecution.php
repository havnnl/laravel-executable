<?php

declare(strict_types=1);

namespace Havn\Executable;

use Closure;
use Havn\Executable\Config\QueueableJobBuilder;
use Havn\Executable\Exceptions\CannotUseConditionalExecution;
use Havn\Executable\Jobs\ExecutableSyncJob;
use Havn\Executable\Testing\Exceptions\CannotQueueMockedExecutable;
use Havn\Executable\Testing\Exceptions\CannotTestExecutable;
use Havn\Executable\Testing\Exceptions\CannotTestMockedExecutable;
use Havn\Executable\Testing\Facades\Execution;
use Havn\Executable\Testing\Spying\ExecutableSpy;
use Illuminate\Queue\Jobs\FakeJob;
use Illuminate\Support\Facades\App;
use Mockery\LegacyMockInterface;

/**
 * @internal
 */
final class PendingExecution
{
    /**
     * @var array<bool|Closure>
     */
    private array $conditions = [];

    private bool $afterResponse = false;

    private ?QueueableJobBuilder $jobBuilder = null;

    private function __construct(
        private object $executable,
        private ExecutionMode $executionType
    ) {}

    private function jobBuilder(): QueueableJobBuilder
    {
        return $this->jobBuilder ??= new QueueableJobBuilder($this->executable, $this->executionType);
    }

    public static function syncFor(object|string $executable): ExecutableSpy|LegacyMockInterface|PendingExecution
    {
        return self::make($executable, ExecutionMode::SYNC);
    }

    public static function queueFor(object|string $executable, ?string $queue = null): PendingExecution
    {
        $instance = self::make($executable, ExecutionMode::QUEUE);

        if ($instance instanceof LegacyMockInterface || $instance instanceof ExecutableSpy) {
            $executableClass = is_string($executable) ? $executable : $executable::class;

            throw new CannotQueueMockedExecutable($executableClass);
        }

        if ($queue !== null) {
            $instance->jobBuilder()->addInvocationCall('onQueue', [$queue]);
        }

        return $instance;
    }

    /**
     * @internal
     */
    public static function prepareFor(object|string $executable): PendingExecution
    {
        $instance = self::make($executable, ExecutionMode::PREPARE);

        if ($instance instanceof LegacyMockInterface || $instance instanceof ExecutableSpy) {
            $executableClass = is_string($executable) ? $executable : $executable::class;

            throw new CannotQueueMockedExecutable($executableClass);
        }

        return $instance;
    }

    /**
     * @internal
     */
    public static function testFor(string $executable): PendingExecution
    {
        if (! App::runningUnitTests()) {
            throw new CannotTestExecutable($executable);
        }

        $instance = self::make($executable, ExecutionMode::TEST);

        if ($instance instanceof LegacyMockInterface || $instance instanceof ExecutableSpy) {
            throw new CannotTestMockedExecutable($executable);
        }

        return $instance;
    }

    public function afterResponse(): static
    {
        $this->afterResponse = true;

        return $this;
    }

    public function when(bool|Closure $condition): static
    {
        if (! in_array($this->executionType, [ExecutionMode::QUEUE, ExecutionMode::SYNC])) {
            throw new CannotUseConditionalExecution('when', $this->executionType);
        }

        $this->conditions[] = $condition;

        return $this;
    }

    public function unless(bool|Closure $condition): static
    {
        if (! in_array($this->executionType, [ExecutionMode::QUEUE, ExecutionMode::SYNC])) {
            throw new CannotUseConditionalExecution('unless', $this->executionType);
        }

        $this->conditions[] = $condition instanceof Closure
            ? fn () => ! $condition()
            : ! $condition;

        return $this;
    }

    public function execute(mixed ...$arguments): mixed
    {
        if (! $this->shouldExecute()) {
            return null;
        }

        return match ($this->executionType) {
            ExecutionMode::SYNC => $this->executeSync($arguments),
            ExecutionMode::TEST => $this->executeTest($arguments),
            ExecutionMode::QUEUE => dispatch($this->jobBuilder()->createJob($arguments)),
            ExecutionMode::PREPARE => $this->jobBuilder()->createJob($arguments),
        };
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    private function executeSync(array $arguments): mixed
    {
        $syncJob = new ExecutableSyncJob($this->executable, $arguments);

        if ($this->afterResponse) {
            dispatch($syncJob)->afterResponse();

            return null;
        }

        return $syncJob->handle();
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    private function executeTest(array $arguments): mixed
    {
        if (! $this->isQueueable()) {
            return (new ExecutableSyncJob($this->executable, $arguments))->handle();
        }

        $job = $this->jobBuilder()->createJob($arguments);
        $job->job = new FakeJob;

        Execution::setTestingJob($job);

        return $job->handle();
    }

    private static function make(object|string $executable, ExecutionMode $type): ExecutableSpy|LegacyMockInterface|PendingExecution
    {
        $instance = is_string($executable) ? resolve($executable) : $executable;

        return match (true) {
            $instance instanceof LegacyMockInterface, $instance instanceof ExecutableSpy => $instance,
            default => new self($instance, $type)
        };
    }

    private function isQueueable(): bool
    {
        return in_array(QueueableExecutable::class, class_uses_recursive($this->executable));
    }

    private function shouldExecute(): bool
    {
        foreach ($this->conditions as $condition) {
            $result = $condition instanceof Closure
                ? $condition()
                : $condition;

            if (! $result) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->jobBuilder()->addInvocationCall($name, $arguments)
            ? $this
            : $this->executable->$name(...$arguments);
    }
}
