<?php

declare(strict_types=1);

namespace Havn\Executable\Jobs;

use DateTimeInterface;
use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Contracts\ShouldExecuteInTransaction;
use Havn\Executable\QueueableExecutable;
use Havn\Executable\Support\InvokesExecutableMethods;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Throwable;

/**
 * @internal
 */
class ExecutableJob implements ShouldQueue
{
    use Batchable,
        InteractsWithQueue,
        InvokesExecutableMethods,
        Queueable,
        SerializesModels {
            __serialize as __parentSerialize;
            __unserialize as __parentUnserialize;
        }

    public ?int $tries = null;

    public ?int $maxExceptions = null;

    public DateTimeInterface|int|null $retryUntil = null;

    /** @var array<int, int>|int|null */
    public array|int|null $backoff = null;

    public ?int $timeout = null;

    public ?bool $failOnTimeout = null;

    public ?bool $shouldBeEncrypted = null;

    public ?bool $deleteWhenMissingModels = null;

    public ?string $displayName = null;

    public ?bool $withoutRelations = null;

    protected string $executableClass;

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function __construct(protected object $executable, protected array $arguments, ?QueueableConfig $config)
    {
        $this->executableClass = get_class($executable);

        $this->updateJobConfig($executable, $config);
    }

    public function executableClass(): string
    {
        return $this->executableClass;
    }

    public function executable(): object
    {
        return $this->executable;
    }

    /**
     * @return array<string, mixed>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    public function handle(): mixed
    {
        $this->setJobOnExecutable();

        if (! $this->executable instanceof ShouldExecuteInTransaction) {
            return $this->executable->execute(...$this->arguments);
        }

        $attempts = property_exists($this->executable, 'transactionAttempts')
            ? $this->executable->transactionAttempts
            : 1;

        return DB::transaction(function (): mixed {
            return $this->executable->execute(...$this->arguments);
        }, $attempts);
    }

    public function displayName(): string
    {
        return $this->displayName ?: $this->executableClass();
    }

    /**
     * Laravel Horizon tag support
     *
     * @return list<string>|null
     */
    public function tags(): ?array
    {
        if (method_exists($this->executable, 'tags')) {
            return $this->invoke($this->executable, 'tags', $this->arguments);
        }

        return collect(array_values($this->arguments))
            ->map(function ($argument) {
                if ($argument instanceof Model) {
                    return [$argument];
                } elseif ($argument instanceof EloquentCollection) {
                    return $argument->all();
                }
            })
            ->collapse()
            ->filter()
            ->map(fn (Model $model) => get_class($model).':'.$model->getKey())
            ->values()
            ->toArray() ?: null;
    }

    /**
     * @return array<int, mixed>
     */
    public function middleware(): array
    {
        return method_exists($this->executable, 'middleware')
            ? $this->invoke($this->executable, 'middleware', $this->arguments)
            : [];
    }

    public function failed(Throwable $throwable): void
    {
        if (method_exists($this->executable, 'failed')) {
            $reflection = new ReflectionMethod($this->executable, 'failed');
            $firstParam = $reflection->getParameters()[0] ?? null;
            $throwableArgs = $firstParam ? [$firstParam->getName() => $throwable] : [];

            $this->invoke(
                $this->executable,
                'failed',
                array_merge($throwableArgs, array_diff_key($this->arguments, $throwableArgs)),
            );
        }
    }

    private function updateJobConfig(object $executable, ?QueueableConfig $config): void
    {
        if (property_exists($executable, 'middleware')) {
            $this->middleware = $executable->middleware;
        }

        if (! $config) {
            return;
        }

        // Use Queueable trait methods where available for proper encapsulation
        $this->onConnection($config->connection);
        $this->onQueue($config->queue);
        $this->delay($config->delay);
        $this->chain($config->chain);

        match ($config->afterCommit) {
            true => $this->afterCommit(),
            false => $this->beforeCommit(),
            null => null,
        };

        // Direct property assignment for properties without setter methods
        $this->displayName = $config->displayName;
        $this->tries = $config->tries;
        $this->retryUntil = $config->retryUntil;
        $this->backoff = $config->backoff;
        $this->maxExceptions = $config->maxExceptions;
        $this->timeout = $config->timeout;
        $this->failOnTimeout = $config->failOnTimeout;
        $this->shouldBeEncrypted = $config->shouldBeEncrypted;
        $this->deleteWhenMissingModels = $config->deleteWhenMissingModels;
        $this->withoutRelations = $config->withoutRelations;
        $this->chainConnection = $config->chainConnection;
        $this->chainQueue = $config->chainQueue;
    }

    /**
     * Injects this job instance into the executable's protected $executableJob
     * property. It keeps the public API clean while allowing executables to
     * work with the underlying job. Inspired by Spatie's invade package.
     *
     * @see QueueableExecutable::$executableJob
     */
    private function setJobOnExecutable(): void
    {
        if (property_exists($this->executable, 'executableJob')) {
            $executableJob = $this;
            $executable = $this->executable;

            /**
             * @see https://github.com/spatie/invade
             *
             * @var QueueableExecutable $this
             */
            (fn () => $this->executableJob = $executableJob)->call($executable); // @phpstan-ignore-line
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        $executable = $this->executable;

        unset($this->executable); // @phpstan-ignore unset.possiblyHookedProperty

        $withRelations = $this->withoutRelations === null
            ? config('executable.serialize_models_with_relations')
            : ! $this->withoutRelations;

        foreach ($this->arguments as $key => $value) {
            $this->arguments[$key] = $this->getSerializedPropertyValue($value, $withRelations);
        }

        $data = $this->__parentSerialize();

        $this->executable = $executable;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function __unserialize(array $values): void
    {
        $this->__parentUnserialize($values);

        $this->executable = resolve($this->executableClass());

        foreach ($this->arguments as $key => $value) {
            $this->arguments[$key] = $this->getRestoredPropertyValue($value);
        }
    }
}
