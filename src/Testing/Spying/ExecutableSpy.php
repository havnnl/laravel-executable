<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Spying;

use Havn\Executable\Testing\Exceptions\CannotSpyExecutable;
use Illuminate\Support\Facades\App;
use Mockery;
use Mockery\LegacyMockInterface;

final class ExecutableSpy
{
    private LegacyMockInterface $spiedExecutable;

    /**
     * Prevents premature garbage collection of spy expectation objects.
     * Without these references, destructors run before tests finish.
     * This will keep expectation objects alive until end of test.
     *
     * @var array<int, ShouldExecuteSpyExpectation|ShouldNeverExecuteSpyExpectation>
     */
    private array $expectationReferences = [];

    public function __construct(object $executable)
    {
        if (! App::runningUnitTests()) {
            throw new CannotSpyExecutable($executable::class);
        }

        $this->spiedExecutable = Mockery::spy($executable)->makePartial();
    }

    /**
     * @param  class-string  $executableClass
     */
    public static function for(string $executableClass): ExecutableSpy
    {
        $instance = resolve($executableClass);

        if ($instance instanceof ExecutableSpy) {
            return $instance;
        }

        $spiedExecutable = new self($instance);

        App::instance($executableClass, $spiedExecutable);

        return $spiedExecutable;
    }

    public function shouldExecute(): ShouldExecuteSpyExpectation
    {
        return $this->expectationReferences[] = new ShouldExecuteSpyExpectation($this->spiedExecutable);
    }

    public function shouldNeverExecute(): ShouldNeverExecuteSpyExpectation
    {
        return $this->expectationReferences[] = new ShouldNeverExecuteSpyExpectation($this->spiedExecutable);
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->spiedExecutable->$name(...$arguments);
    }

    /**
     * Implemented to allow for manual garbage collection of expectation
     * objects, only for internal package testing, not for userland code.
     */
    public function __destruct()
    {
        unset($this->expectationReferences);
    }
}
