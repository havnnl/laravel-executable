<?php

declare(strict_types=1);

namespace Havn\Executable\Support;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

/**
 * @internal
 */
final class ExecutableArguments
{
    use SerializesAndRestoresModelIdentifiers;

    /**
     * @param  array<int|string, mixed>  $arguments
     */
    private function __construct(private array $arguments) {}

    /**
     * @param  array<int, mixed>  $arguments
     */
    public static function resolve(object $executable, array $arguments): self
    {
        if (! method_exists($executable, 'execute')) {
            throw new RuntimeException('Executable must have an execute() method.');
        }

        $parameters = (new ReflectionClass($executable))
            ->getMethod('execute')
            ->getParameters();

        $lastParam = end($parameters);
        $isVariadic = $lastParam && $lastParam->isVariadic();
        $nonVariadicParams = $isVariadic ? array_slice($parameters, 0, -1) : $parameters;

        $resolved = [];
        $positionalIndex = 0;

        foreach ($nonVariadicParams as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $arguments)) {
                $resolved[$name] = $arguments[$name];
            } elseif (array_key_exists($positionalIndex, $arguments)) {
                $resolved[$name] = $arguments[$positionalIndex];
                $positionalIndex++;
            }
        }

        while ($isVariadic && array_key_exists($positionalIndex, $arguments)) {
            $resolved[$positionalIndex] = $arguments[$positionalIndex];
            $positionalIndex++;
        }

        return new self($resolved);
    }

    /**
     * @param  array<int|string, mixed>  $arguments
     */
    public static function from(array $arguments): self
    {
        return new self($arguments);
    }

    public function callOn(object $target, string $method): mixed
    {
        $filtered = $this->filterPartiallyVariadicArguments($target, $method);

        try {
            return app()->call([$target, $method], $filtered);
        } catch (BindingResolutionException) {
            return $target->{$method}(...array_values($filtered));
        }
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function with(array $arguments): self
    {
        return new self($arguments + $this->arguments);
    }

    /**
     * @return array<int|string, mixed>
     */
    public function serialize(bool $withRelations = true): array
    {
        return array_map(
            fn (mixed $value): mixed => $this->getSerializedPropertyValue($value, $withRelations),
            $this->arguments,
        );
    }

    /**
     * @param  array<int|string, mixed>  $arguments
     */
    public static function unserialize(array $arguments): self
    {
        $instance = new self($arguments);

        foreach ($instance->arguments as $key => $value) {
            $instance->arguments[$key] = $instance->getRestoredPropertyValue($value);
        }

        return $instance;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function toArray(): array
    {
        return $this->arguments;
    }

    /**
     * @return list<mixed>
     */
    public function values(): array
    {
        return array_values($this->arguments);
    }

    /**
     * When execute() mixes named and variadic params (e.g. `execute(string $name, string ...$input)`),
     * resolved arguments contain both string-keyed and int-keyed entries. In that case we need to
     * filter out string-keyed entries that don't match the target method's parameters, so we
     * prevent them from being passed as extra positional args by app()->call().
     *
     * @return array<int|string, mixed>
     */
    private function filterPartiallyVariadicArguments(object $target, string $method): array
    {
        // $this->arguments is empty or all values are positional
        if (array_is_list($this->arguments)) {
            return $this->arguments;
        }

        // all values are string-keyed
        if (! array_filter(array_keys($this->arguments), 'is_int')) {
            return $this->arguments;
        }

        $parameters = (new ReflectionMethod($target, $method))->getParameters();

        $paramNames = [];

        foreach ($parameters as $parameter) {
            if ($parameter->isVariadic()) {
                break;
            }

            $paramNames[] = $parameter->getName();
        }

        return array_filter(
            $this->arguments,
            fn (int|string $key): bool => is_int($key) || in_array($key, $paramNames, true),
            ARRAY_FILTER_USE_KEY,
        );
    }
}
