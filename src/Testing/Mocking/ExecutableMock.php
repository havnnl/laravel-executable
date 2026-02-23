<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Mocking;

use Havn\Executable\Testing\Exceptions\CannotMockExecutable;
use Illuminate\Support\Facades\App;
use Mockery;
use Mockery\Expectation;
use Mockery\ExpectationInterface;
use Mockery\HigherOrderMessage;
use Mockery\LegacyMockInterface;

final class ExecutableMock
{
    private function __construct(private LegacyMockInterface $mock) {}

    /**
     * @param  class-string  $executableClass
     */
    public static function for(string $executableClass): ExecutableMock
    {
        if (! App::runningUnitTests()) {
            throw new CannotMockExecutable($executableClass);
        }

        $mock = resolve($executableClass);

        $mock = $mock instanceof LegacyMockInterface
            ? $mock
            : App::instance($executableClass, Mockery::mock($executableClass));

        $mock->shouldReceive('sync')->andReturnSelf();

        return new self($mock);
    }

    public function shouldExecute(): Expectation|ExpectationInterface|HigherOrderMessage
    {
        return $this->mock->shouldReceive('execute');
    }

    public function shouldNeverExecute(): void
    {
        $this->mock->shouldNotReceive('execute');
    }
}
