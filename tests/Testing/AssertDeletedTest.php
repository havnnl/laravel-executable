<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\PlainQueueableExecutable;
use Workbench\App\Executables\Testing\DeletedExecutable;

it('fails when executable is not tested', function () {
    DeletedExecutable::sync()->execute();

    expect(fn () => DeletedExecutable::assert()->deleted())
        ->toThrow(ExpectationFailedException::class, 'Testing not active for [DeletedExecutable]. Use [DeletedExecutable::test()]');
});

it('passes when executable is deleted', function () {
    DeletedExecutable::test()->execute();

    DeletedExecutable::assert()->deleted();
});

it('fails when executable is not deleted', function () {
    PlainQueueableExecutable::test()->execute('some input');

    expect(fn () => PlainQueueableExecutable::assert()->deleted())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was expected to be deleted, but was not.');
});
