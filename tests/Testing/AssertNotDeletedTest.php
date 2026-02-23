<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\PlainQueueableExecutable;
use Workbench\App\Executables\Testing\DeletedExecutable;

it('fails when executable is not tested', function () {
    PlainQueueableExecutable::sync()->execute('some input');

    expect(fn () => PlainQueueableExecutable::assert()->notDeleted())
        ->toThrow(ExpectationFailedException::class, 'Testing not active for [PlainQueueableExecutable]. Use [PlainQueueableExecutable::test()]');
});

it('passes when executable is not deleted', function () {
    PlainQueueableExecutable::test()->execute('some input');

    PlainQueueableExecutable::assert()->notDeleted();
});

it('fails when executable is deleted', function () {
    DeletedExecutable::test()->execute();

    expect(fn () => DeletedExecutable::assert()->notDeleted())
        ->toThrow(ExpectationFailedException::class, '[DeletedExecutable] was deleted unexpectedly.');
});
