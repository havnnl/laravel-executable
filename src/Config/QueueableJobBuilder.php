<?php

declare(strict_types=1);

namespace Havn\Executable\Config;

use Havn\Executable\ExecutionMode;
use Havn\Executable\Jobs\ExecutableJob;
use Havn\Executable\Jobs\ExecutableUniqueJob;
use Havn\Executable\Jobs\ExecutableUniqueUntilProcessingJob;
use Havn\Executable\Support\InvokesExecutableMethods;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\WithoutRelations;
use ReflectionClass;
use RuntimeException;

/**
 * @internal
 */
final class QueueableJobBuilder
{
    use InvokesExecutableMethods;

    /** @var array<string, array<int, mixed>> */
    private array $invocationCalls = [];

    /** @var ReflectionClass<object>|null */
    private ?ReflectionClass $reflection = null;

    /** @var ?array<string, mixed> */
    private ?array $namedArguments = null;

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

    /**
     * @param  array<int|string, mixed>  $arguments
     */
    public function createJob(array $arguments): ExecutableJob
    {
        $config = $this->buildConfig($arguments);

        $namedArguments = $this->namedArguments();

        return match (true) {
            $config->shouldBeUniqueUntilProcessing => new ExecutableUniqueUntilProcessingJob($this->executable, $namedArguments, $config),
            $config->shouldBeUnique => new ExecutableUniqueJob($this->executable, $namedArguments, $config),
            default => new ExecutableJob($this->executable, $namedArguments, $config),
        };
    }

    /**
     * @param  array<int|string, mixed>  $arguments
     */
    private function buildConfig(array $arguments): QueueableConfig
    {
        $this->buildNamedArguments($arguments);

        $config = $this->initConfigFromStatics();

        $this->applyConfigFromHook($config);

        $this->applyConfigFromMethods($config);

        $this->applyConfigFromInvocation($config);

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    private function namedArguments(): array
    {
        return is_array($this->namedArguments)
            ? $this->namedArguments
            : throw new RuntimeException('Named arguments have not been built yet.');
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

    private function applyConfigFromHook(QueueableConfig $config): void
    {
        if (method_exists($this->executable, 'configure')) {
            $this->reflection ??= new ReflectionClass($this->executable);
            $firstParam = $this->reflection->getMethod('configure')->getParameters()[0] ?? null;
            $configArgs = $firstParam ? [$firstParam->getName() => $config] : [];

            $this->invoke(
                $this->executable,
                'configure',
                array_merge($configArgs, array_diff_key($this->namedArguments(), $configArgs)),
            );
        }
    }

    private function applyConfigFromMethods(QueueableConfig $config): void
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
                $config->$method = $this->invoke($this->executable, $method, $this->namedArguments());
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

    /**
     * @param  array<int|string, mixed>  $arguments
     */
    private function buildNamedArguments(array $arguments): void
    {
        if (! method_exists($this->executable, 'execute')) {
            throw new RuntimeException('Executable must have an execute() method.');
        }

        $this->reflection ??= new ReflectionClass($this->executable);
        $parameters = $this->reflection->getMethod('execute')->getParameters();

        $positionalIndex = 0;

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $arguments)) {
                $this->namedArguments[$name] = $arguments[$name];
            } elseif (array_key_exists($positionalIndex, $arguments)) {
                $this->namedArguments[$name] = $arguments[$positionalIndex];
                $positionalIndex++;
            }
        }

        $this->namedArguments ??= [];
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
