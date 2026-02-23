<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\PlainQueueableExecutable;
use Workbench\App\Executables\Testing\ReleasedExecutable;

it('fails when executable is not tested', function () {
    ReleasedExecutable::sync()->execute('some input');

    expect(fn () => ReleasedExecutable::assert()->released())
        ->toThrow(ExpectationFailedException::class, 'Testing not active for [ReleasedExecutable]. Use [ReleasedExecutable::test()]');
});

it('passes when executable is released', function () {
    ReleasedExecutable::test()->execute('some input');

    ReleasedExecutable::assert()->released();
});

it('fails when executable is not released', function () {
    PlainQueueableExecutable::test()->execute('some input');

    expect(fn () => PlainQueueableExecutable::assert()->released())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was expected to be released, but was not.');
});

it('passes when executable is released with expected delay', function () {
    ReleasedExecutable::test()->execute('some input');

    ReleasedExecutable::assert()->released(5);
});

it('passes when executable is released with expected delay ', function () {
    ReleasedExecutable::test()->execute('some input');

    ReleasedExecutable::assert()->released(now()->addSeconds(5));
});

it('fails when executable is not released with expected delay', function () {
    ReleasedExecutable::test()->execute('some input');

    expect(fn () => ReleasedExecutable::assert()->released(10))
        ->toThrow(ExpectationFailedException::class, 'Expected [ReleasedExecutable] to be released with delay of [10] seconds, but was released with delay of [5] seconds.');
});

it('passes when executable is not released', function () {
    PlainQueueableExecutable::test()->execute('some input');

    PlainQueueableExecutable::assert()->notReleased();
});

it('fails when executable is released', function () {
    ReleasedExecutable::test()->execute('some input');
    expect(fn () => ReleasedExecutable::assert()->notReleased())
        ->toThrow(ExpectationFailedException::class, '[ReleasedExecutable] was released unexpectedly.');
});
