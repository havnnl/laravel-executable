<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Spying;

use Mockery\LegacyMockInterface;
use Mockery\VerificationDirector;

/**
 * @mixin VerificationDirector
 */
final class ShouldExecuteSpyExpectation
{
    /** @var array<int, array{name: string, arguments: array<int, mixed>}> */
    private array $calls = [];

    private bool $verified = false;

    public function __construct(private readonly LegacyMockInterface $spy) {}

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $name, array $arguments): self
    {
        $this->calls[] = [
            'name' => $name,
            'arguments' => $arguments,
        ];

        return $this;
    }

    /**
     * Verifies expectations via Mockery.
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

        $spy = $this->spy->shouldHaveReceived('execute');

        foreach ($this->calls as $call) {
            $spy = $spy->{$call['name']}(...$call['arguments']);
        }
    }

    public function __destruct()
    {
        $this->verify();
    }
}
