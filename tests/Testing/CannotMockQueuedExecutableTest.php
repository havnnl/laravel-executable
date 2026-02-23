<?php

declare(strict_types=1);

use Havn\Executable\Testing\Exceptions\CannotQueueMockedExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;

it('throws exception when attempting to queue mocked execution', function () {
    PlainQueueableExecutable::mock();

    expect(fn () => PlainQueueableExecutable::onQueue()->execute())
        ->toThrow(
            CannotQueueMockedExecutable::class,
            'Cannot queue mocked executable [Workbench\App\Executables\PlainQueueableExecutable].'
        );
});

it('throws exception when attempting to prepare mocked execution', function () {
    PlainQueueableExecutable::mock();

    expect(fn () => PlainQueueableExecutable::prepare()->execute())
        ->toThrow(
            CannotQueueMockedExecutable::class,
            'Cannot queue mocked executable [Workbench\App\Executables\PlainQueueableExecutable].'
        );
});

it('throws exception when attempting to queue spied execution', function () {
    PlainQueueableExecutable::spy();

    expect(fn () => PlainQueueableExecutable::onQueue()->execute())
        ->toThrow(
            CannotQueueMockedExecutable::class,
            'Cannot queue mocked executable [Workbench\App\Executables\PlainQueueableExecutable].'
        );
});

it('throws exception when attempting to prepare spied execution', function () {
    PlainQueueableExecutable::spy();

    expect(fn () => PlainQueueableExecutable::prepare()->execute())
        ->toThrow(
            CannotQueueMockedExecutable::class,
            'Cannot queue mocked executable [Workbench\App\Executables\PlainQueueableExecutable].'
        );
});

it('allows mocking synchronous execution', function () {
    PlainQueueableExecutable::mock()
        ->shouldExecute()
        ->once()
        ->andReturn('mocked');

    $result = PlainQueueableExecutable::sync()->execute();

    expect($result)->toBe('mocked');
});
