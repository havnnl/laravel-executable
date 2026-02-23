<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\PlainQueueableExecutable;
use Workbench\App\Executables\Testing\FailedExecutable;

it('fails when executable is not tested', function () {
    PlainQueueableExecutable::sync()->execute();

    expect(fn () => PlainQueueableExecutable::assert()->notFailed())
        ->toThrow(ExpectationFailedException::class, 'Testing not active for [PlainQueueableExecutable]. Use [PlainQueueableExecutable::test()]');
});

it('passes when executable is not failed', function () {
    PlainQueueableExecutable::test()->execute();

    PlainQueueableExecutable::assert()->notFailed();
});

it('fails when executable is failed', function () {
    FailedExecutable::test()->execute('some input');

    expect(fn () => FailedExecutable::assert()->notFailed())
        ->toThrow(ExpectationFailedException::class, '[FailedExecutable] was manually failed unexpectedly.');
});
