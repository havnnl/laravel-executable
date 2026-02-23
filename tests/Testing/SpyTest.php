<?php

declare(strict_types=1);

use Havn\Executable\Testing\Exceptions\CannotSpyExecutable;
use Illuminate\Support\Facades\App;
use Mockery\Exception\InvalidCountException;
use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\InputConcatenatingExecutable;

describe('Spy Setup', function () {
    it('cannot spy when not testing', function () {
        App::shouldReceive('runningUnitTests')->andReturnFalse();

        InputConcatenatingExecutable::spy();
    })->throws(CannotSpyExecutable::class);

    it('fails when executable not spied', function () {
        InputConcatenatingExecutable::sync()->execute('input');

        expect(fn () => InputConcatenatingExecutable::assert()->executed()->with('input')->once())
            ->toThrow(ExpectationFailedException::class, sprintf(
                'Spy not active for [%s]. Use [%s::spy()]',
                InputConcatenatingExecutable::class, InputConcatenatingExecutable::class
            ));
    });
});

describe('Pre-execution Expectations', function () {
    describe('passing scenarios', function () {
        it('verifies execution without count', function () {
            InputConcatenatingExecutable::spy()->shouldExecute();

            $result = InputConcatenatingExecutable::sync()->execute('input');

            expect($result)->toBe('input');
        });

        it('verifies with specific input', function () {
            InputConcatenatingExecutable::spy()->shouldExecute()->with('input')->once();

            $result = InputConcatenatingExecutable::sync()->execute('input');

            expect($result)->toBe('input');
        });

        it('verifies call count', function () {
            InputConcatenatingExecutable::spy()->shouldExecute()->times(2);

            $result1 = InputConcatenatingExecutable::sync()->execute('input1');
            $result2 = InputConcatenatingExecutable::sync()->execute('input2');

            expect($result1)->toBe('input1')
                ->and($result2)->toBe('input2');
        });

        it('verifies call count with specific input', function () {
            InputConcatenatingExecutable::spy()->shouldExecute()->with('input1')->times(2);
            InputConcatenatingExecutable::spy()->shouldExecute()->with('input2')->once();

            $result1a = InputConcatenatingExecutable::sync()->execute('input1');
            $result1b = InputConcatenatingExecutable::sync()->execute('input1');
            $result2 = InputConcatenatingExecutable::sync()->execute('input2');

            expect($result1a)->toBe('input1')
                ->and($result1b)->toBe('input1')
                ->and($result2)->toBe('input2');
        });

        it('verifies shouldNeverExecute with args', function () {
            InputConcatenatingExecutable::spy()->shouldNeverExecute()->with('forbidden');

            $result = InputConcatenatingExecutable::sync()->execute('allowed');

            expect($result)->toBe('allowed');
        });

        it('verifies shouldNeverExecute with args closure', function () {
            InputConcatenatingExecutable::spy()->shouldNeverExecute()->withArgs(fn ($input) => $input === 'forbidden');

            $result = InputConcatenatingExecutable::sync()->execute('allowed');

            expect($result)->toBe('allowed');
        });
    });

    describe('failing scenarios', function () {
        it('fails pre-execution execution without count when not executed', function () {
            $expectation = InputConcatenatingExecutable::spy()->shouldExecute();

            expect(fn () => $expectation->__destruct())
                ->toThrow(InvalidCountException::class);
        });

        it('fails pre-execution expectation with count when not executed', function () {
            $expectation = InputConcatenatingExecutable::spy()->shouldExecute()->once();

            InputConcatenatingExecutable::sync();

            expect(fn () => $expectation->__destruct())
                ->toThrow(InvalidCountException::class);
        });

        it('fails pre-execution expectation with wrong input', function () {
            $expectation = InputConcatenatingExecutable::spy()->shouldExecute()->with('expected')->once();

            InputConcatenatingExecutable::sync()->execute('actual');

            expect(fn () => $expectation->__destruct())
                ->toThrow(InvalidCountException::class);
        });

        it('fails pre-execution expectation when executed too few times', function () {
            $expectation = InputConcatenatingExecutable::spy()->shouldExecute()->times(3);

            InputConcatenatingExecutable::sync()->execute('input');
            InputConcatenatingExecutable::sync()->execute('input');

            expect(fn () => $expectation->__destruct())
                ->toThrow(InvalidCountException::class);
        });

        it('fails pre-execution expectation when executed too many times', function () {
            $expectation = InputConcatenatingExecutable::spy()->shouldExecute()->once();

            InputConcatenatingExecutable::sync()->execute('input');
            InputConcatenatingExecutable::sync()->execute('input');

            expect(fn () => $expectation->__destruct())
                ->toThrow(InvalidCountException::class);
        });

        it('fails when shouldNeverExecute is violated with args', function () {
            $spy = InputConcatenatingExecutable::spy()->shouldNeverExecute();
            $spy->with('forbidden');

            InputConcatenatingExecutable::sync()->execute('forbidden');

            expect(fn () => $spy->__destruct())
                ->toThrow(InvalidCountException::class);
        });

        it('fails when shouldNeverExecute is violated with args closure', function () {
            $spy = InputConcatenatingExecutable::spy()->shouldNeverExecute();
            $spy->withArgs(fn ($input) => $input === 'forbidden');

            InputConcatenatingExecutable::sync()->execute('forbidden');

            expect(fn () => $spy->__destruct())
                ->toThrow(InvalidCountException::class);
        });
    });
});

describe('Post-execution Assertions', function () {
    describe('passing scenarios', function () {
        it('verifies execution', function () {
            InputConcatenatingExecutable::spy();

            $result = InputConcatenatingExecutable::sync()->execute('input');

            expect($result)->toBe('input');

            InputConcatenatingExecutable::assert()->executed();
        });

        it('verifies non-execution', function () {
            InputConcatenatingExecutable::spy();

            InputConcatenatingExecutable::assert()->notExecuted();
        });
    });

    describe('failing scenarios', function () {
        it('fails post-execution assertion when not executed', function () {
            InputConcatenatingExecutable::spy();

            InputConcatenatingExecutable::sync();

            InputConcatenatingExecutable::assert()->executed()->once();

            InputConcatenatingExecutable::spy()->__destruct();
        })->throws(InvalidCountException::class);

        it('fails post-execution assertion with wrong input', function () {
            InputConcatenatingExecutable::spy();

            InputConcatenatingExecutable::sync()->execute('actual');

            InputConcatenatingExecutable::assert()->executed()->with('expected')->once();

            InputConcatenatingExecutable::spy()->__destruct();
        })->throws(InvalidCountException::class);

        it('fails post-execution assertion when executed too few times', function () {
            InputConcatenatingExecutable::spy();

            InputConcatenatingExecutable::sync()->execute('input');
            InputConcatenatingExecutable::sync()->execute('input');

            InputConcatenatingExecutable::assert()->executed()->times(3);

            InputConcatenatingExecutable::spy()->__destruct();
        })->throws(InvalidCountException::class);

        it('fails post-execution assertion when executed too many times', function () {
            InputConcatenatingExecutable::spy();

            InputConcatenatingExecutable::sync()->execute('input');
            InputConcatenatingExecutable::sync()->execute('input');

            InputConcatenatingExecutable::assert()->executed()->once();

            InputConcatenatingExecutable::spy()->__destruct();
        })->throws(InvalidCountException::class);

        it('fails when notExecuted is violated', function () {
            InputConcatenatingExecutable::spy();

            InputConcatenatingExecutable::sync()->execute('input');

            InputConcatenatingExecutable::assert()->notExecuted();

            InputConcatenatingExecutable::spy()->__destruct();
        })->throws(InvalidCountException::class);

        it('fails via container injection when not executed', function () {
            InputConcatenatingExecutable::spy();

            app(InputConcatenatingExecutable::class);

            InputConcatenatingExecutable::assert()->executed()->once();

            InputConcatenatingExecutable::spy()->__destruct();
        })->throws(InvalidCountException::class);

        it('fails via container injection when unexpectedly executes', function () {
            InputConcatenatingExecutable::spy();

            app(InputConcatenatingExecutable::class)->execute('input');

            InputConcatenatingExecutable::assert()->notExecuted();

            InputConcatenatingExecutable::spy()->__destruct();
        })->throws(InvalidCountException::class);
    });
});
