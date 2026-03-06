<?php

declare(strict_types=1);

namespace Havn\Executable\Support;

use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
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

        $executeParameters = (new ReflectionMethod($executable, 'execute'))->getParameters();

        $resolved = [];
        $positionalArguments = array_values(array_filter($arguments, is_int(...), ARRAY_FILTER_USE_KEY));

        foreach ($executeParameters as $executeParameter) {
            if ($executeParameter->isVariadic()) {
                break;
            }

            $name = $executeParameter->getName();

            if (array_key_exists($name, $arguments)) {
                $resolved[$name] = $arguments[$name];
            } elseif ($positionalArguments) {
                $resolved[$name] = array_shift($positionalArguments);
            }
        }

        array_push($resolved, ...$positionalArguments);

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
        $parameters = (new ReflectionMethod($target, $method))->getParameters();

        $methodNamedArguments = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $this->arguments)) {
                $methodNamedArguments[] = $this->arguments[$name];
            } else {
                break;
            }
        }

        try {
            return $target->{$method}(...$methodNamedArguments, ...$this->variadicArguments());
        } catch (\ArgumentCountError) {
            return $target->{$method}(...$this->values());
        }
    }

    /**
     * @return list<mixed>
     */
    private function variadicArguments(): array
    {
        return array_values(array_filter($this->arguments, is_int(...), ARRAY_FILTER_USE_KEY));
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
}
