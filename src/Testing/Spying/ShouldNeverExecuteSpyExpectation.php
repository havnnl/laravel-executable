<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Spying;

use Closure;
use Mockery\LegacyMockInterface;

final class ShouldNeverExecuteSpyExpectation
{
    /** @var array<int, mixed>|Closure|null */
    private array|Closure|null $arguments = null;

    private bool $verified = false;

    public function __construct(private readonly LegacyMockInterface $spy) {}

    public function with(mixed ...$args): void
    {
        $this->arguments = $args;
    }

    /**
     * @param  array<int, mixed>|Closure  $argsOrClosure
     */
    public function withArgs(array|Closure $argsOrClosure): void
    {
        $this->arguments = $argsOrClosure;
    }

    /**
     * Verifies expectation via Mockery.
     *
     * The $verified flag prevents double-verification when tests manually call
     * __destruct() and PHP calls it again automatically. Manual __destruct()
     * calls are only for internal package testing, not for userland code.
     */
    private function verify(): void
    {
        if ($this->verified) {
            return;
        }

        $this->verified = true;

        is_null($this->arguments)
            ? $this->spy->shouldNotHaveReceived('execute')
            : $this->spy->shouldNotHaveReceived('execute', $this->arguments);
    }

    public function __destruct()
    {
        $this->verify();
    }
}
