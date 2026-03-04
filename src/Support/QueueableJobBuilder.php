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
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\WithoutRelations;
use ReflectionClass;

/**
 * @internal
 */
final class QueueableJobBuilder
{
    /** @var array<string, array<int, mixed>> */
    private array $invocationCalls = [];

    /** @var ReflectionClass<object>|null */
    private ?ReflectionClass $reflection = null;

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
            backoff: $this->readProperty('backoff'),
            chainConnection: $this->readProperty('chainConnection'),
            chainQueue: $this->readProperty('chainQueue'),
            connection: $this->readProperty('connection'),
            delay: $this->readProperty('delay'),
            deleteWhenMissingModels: $this->hasAttributeOrNull(DeleteWhenMissingModels::class) ?? $this->readProperty('deleteWhenMissingModels'),
            failOnTimeout: $this->readProperty('failOnTimeout'),
            maxExceptions: $this->readProperty('maxExceptions'),
            queue: $this->readProperty('queue'),
            retryUntil: $this->readProperty('retryUntil'),
            shouldBeEncrypted: $this->executable instanceof ShouldBeEncrypted ?: $this->readProperty('shouldBeEncrypted'),
            shouldBeUnique: $this->executable instanceof ShouldBeUnique && ! $this->executable instanceof ShouldBeUniqueUntilProcessing,
            shouldBeUniqueUntilProcessing: $this->executable instanceof ShouldBeUniqueUntilProcessing,
            timeout: $this->readProperty('timeout'),
            tries: $this->readProperty('tries'),
            uniqueFor: $this->readProperty('uniqueFor'),
            uniqueId: $this->readProperty('uniqueId'),
            withoutRelations: $this->hasAttributeOrNull(WithoutRelations::class) ?? $this->readProperty('withoutRelations'),
        );
    }

    private function applyConfigFromHook(QueueableConfig $config, ExecutableArguments $arguments): void
    {
        if (method_exists($this->executable, 'configure')) {
            $this->reflection ??= new ReflectionClass($this->executable);
            $firstParam = $this->reflection->getMethod('configure')->getParameters()[0] ?? null;
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
        return property_exists($this->executable, $property)
            ? $this->executable->{$property}
            : null;
    }

    private function hasAttributeOrNull(string $attributeClass): ?bool
    {
        $this->reflection ??= new ReflectionClass($this->executable);

        return ! empty($this->reflection->getAttributes($attributeClass)) ? true : null;
    }
}
