<?php

declare(strict_types=1);

namespace Havn\Executable\Support;

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\ExecutionMode;
use Havn\Executable\Jobs\ExecutableJob;
use Havn\Executable\Jobs\ExecutableUniqueJob;
use Havn\Executable\Jobs\ExecutableUniqueUntilProcessingJob;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Connection;
use Illuminate\Queue\Attributes\Delay;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\FailOnTimeout;
use Illuminate\Queue\Attributes\MaxExceptions;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\UniqueFor;
use Illuminate\Queue\Attributes\WithoutRelations;
use ReflectionClass;

/**
 * @internal
 */
final class QueueableJobBuilder
{
    /** @var array<string, array<int, mixed>> */
    private array $invocationCalls = [];

    public function __construct(
        private object $executable,
        private ExecutionMode $executionType
    ) {}

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function addInvocationCall(string $method, array $arguments): bool
    {
        if (! $this->executionType->hasInvocationMethod($method)) {
            return false;
        }

        $this->invocationCalls[$method] = $arguments;

        return true;
    }

    public function createJob(ExecutableArguments $arguments): ExecutableJob
    {
        $config = $this->buildConfig($arguments);

        return match (true) {
            $config->shouldBeUniqueUntilProcessing => new ExecutableUniqueUntilProcessingJob($this->executable, $arguments, $config),
            $config->shouldBeUnique => new ExecutableUniqueJob($this->executable, $arguments, $config),
            default => new ExecutableJob($this->executable, $arguments, $config),
        };
    }

    private function buildConfig(ExecutableArguments $arguments): QueueableConfig
    {
        $config = $this->initConfigFromStatics();

        $this->applyConfigFromHook($config, $arguments);

        $this->applyConfigFromMethods($config, $arguments);

        $this->applyConfigFromInvocation($config);

        return $config;
    }

    private function initConfigFromStatics(): QueueableConfig
    {
        return new QueueableConfig(
            afterCommit: $this->executable instanceof ShouldQueueAfterCommit ?: $this->readProperty('afterCommit'),
            backoff: AttributeReader::resolveValue($this->executable, Backoff::class, 'backoff'),
            chainConnection: $this->readProperty('chainConnection'),
            chainQueue: $this->readProperty('chainQueue'),
            connection: AttributeReader::resolveValue($this->executable, Connection::class, 'connection'),
            delay: AttributeReader::resolveValue($this->executable, Delay::class, 'delay'),
            deleteWhenMissingModels: AttributeReader::resolveValue($this->executable, DeleteWhenMissingModels::class, 'deleteWhenMissingModels'),
            failOnTimeout: AttributeReader::resolveValue($this->executable, FailOnTimeout::class, 'failOnTimeout'),
            maxExceptions: AttributeReader::resolveValue($this->executable, MaxExceptions::class, 'maxExceptions'),
            queue: AttributeReader::resolveValue($this->executable, Queue::class, 'queue'),
            retryUntil: $this->readProperty('retryUntil'),
            shouldBeEncrypted: $this->executable instanceof ShouldBeEncrypted ?: $this->readProperty('shouldBeEncrypted'),
            shouldBeUnique: $this->executable instanceof ShouldBeUnique && ! $this->executable instanceof ShouldBeUniqueUntilProcessing,
            shouldBeUniqueUntilProcessing: $this->executable instanceof ShouldBeUniqueUntilProcessing,
            timeout: AttributeReader::resolveValue($this->executable, Timeout::class, 'timeout'),
            tries: AttributeReader::resolveValue($this->executable, Tries::class, 'tries'),
            uniqueFor: AttributeReader::resolveValue($this->executable, UniqueFor::class, 'uniqueFor'),
            uniqueId: $this->readProperty('uniqueId'),
            withoutRelations: AttributeReader::resolveValue($this->executable, WithoutRelations::class, 'withoutRelations'),
        );
    }

    private function applyConfigFromHook(QueueableConfig $config, ExecutableArguments $arguments): void
    {
        if (method_exists($this->executable, 'configure')) {
            $firstParam = (new ReflectionClass($this->executable))
                ->getMethod('configure')
                ->getParameters()[0] ?? null;
            $configArgs = $firstParam ? [$firstParam->getName() => $config] : [];

            $arguments->with($configArgs)
                ->callOn($this->executable, 'configure');
        }
    }

    private function applyConfigFromMethods(QueueableConfig $config, ExecutableArguments $arguments): void
    {
        $methods = [
            'backoff',
            'displayName',
            'retryUntil',
            'tries',
            'uniqueFor',
            'uniqueId',
        ];

        foreach ($methods as $method) {
            if (method_exists($this->executable, $method)) {
                $config->$method = $arguments->callOn($this->executable, $method);
            }
        }
    }

    private function applyConfigFromInvocation(QueueableConfig $config): void
    {
        $map = [
            'shouldRetryUntil' => 'retryUntil',
            'withBackoff' => 'backoff',
            'withDisplayName' => 'displayName',
            'withTries' => 'tries',
            'withUniqueFor' => 'uniqueFor',
            'withUniqueId' => 'uniqueId',
        ];

        foreach ($this->invocationCalls as $method => $arguments) {

            $configMethod = $map[$method] ?? $method;

            $config->{$configMethod}(...$arguments);
        }
    }

    private function readProperty(string $property): mixed
    {
        return $this->executable->{$property} ?? null;
    }
}
