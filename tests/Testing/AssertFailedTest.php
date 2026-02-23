<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\PlainQueueableExecutable;
use Workbench\App\Executables\Testing\FailedExecutable;

it('fails when executable is not tested', function () {
    FailedExecutable::sync()->execute();

    expect(fn () => FailedExecutable::assert()->failed())
        ->toThrow(ExpectationFailedException::class, 'Testing not active for [FailedExecutable]. Use [FailedExecutable::test()]');
});

it('passes when executable is failed', function () {
    FailedExecutable::test()->execute();

    FailedExecutable::assert()->failed();
});

it('fails when executable is not failed', function () {
    PlainQueueableExecutable::test()->execute('some input');

    expect(fn () => PlainQueueableExecutable::assert()->failed())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was expected to be manually failed, but was not.');
});
