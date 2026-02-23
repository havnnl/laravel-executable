<?php

declare(strict_types=1);

use Havn\Executable\Testing\Exceptions\CannotTestMockedExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;

it('throws exception when attempting to test mocked execution', function () {
    PlainQueueableExecutable::mock();

    expect(fn () => PlainQueueableExecutable::test()->execute())
        ->toThrow(
            CannotTestMockedExecutable::class,
            'Cannot test mocked executable [Workbench\App\Executables\PlainQueueableExecutable].'
        );
});

it('throws exception when attempting to test spied execution', function () {
    PlainQueueableExecutable::spy();

    expect(fn () => PlainQueueableExecutable::test()->execute())
        ->toThrow(
            CannotTestMockedExecutable::class,
            'Cannot test mocked executable [Workbench\App\Executables\PlainQueueableExecutable].'
        );
});
