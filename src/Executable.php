<?php

declare(strict_types=1);

namespace Havn\Executable;

use BadMethodCallException;
use Havn\Executable\Contracts\SyncExecutableMethods;
use Havn\Executable\Contracts\TestedExecutableMethods;
use Havn\Executable\Testing\ExecutionAssertions;
use Havn\Executable\Testing\Mocking\ExecutableMock;
use Havn\Executable\Testing\Spying\ExecutableSpy;

/**
 * @method static static|SyncExecutableMethods sync()
 * @method static ExecutableMock mock()
 * @method static ExecutableSpy spy()
 * @method static static|TestedExecutableMethods test()
 * @method static ExecutionAssertions assert()
 */
trait Executable
{
    /**
     * @param  array<int, mixed>  $arguments
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return match ($name) {
            'sync' => PendingExecution::syncFor(static::class),
            'mock' => ExecutableMock::for(static::class),
            'spy' => ExecutableSpy::for(static::class),
            'test' => PendingExecution::testFor(static::class),
            'assert' => ExecutionAssertions::for(static::class),
            default => throw new BadMethodCallException('Call to undefined method ['.static::class.'::'.$name.'()]'),
        };
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return match ($name) {
            'sync' => PendingExecution::syncFor($this),
            default => throw new BadMethodCallException('Call to undefined method ['.static::class.'::'.$name.'()]'),
        };
    }
}
