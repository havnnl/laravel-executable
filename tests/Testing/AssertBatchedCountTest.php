<?php

declare(strict_types=1);

use Havn\Executable\Testing\Facades\Execution;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\PlainQueueableExecutable;

it('fails when bus not faked', function () {
    expect(fn () => Execution::assertBatchCount(1))
        ->toThrow(ExpectationFailedException::class, 'Bus was not faked. Use [Bus::fake()].');
});

it('passes when x batches dispatched', function () {
    Bus::fake();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute(),
    ])->dispatch();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute(),
    ])->dispatch();

    Execution::assertBatchCount(2);
});

it('fails when x batches not dispatched', function () {
    Bus::fake();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute(),
    ])->dispatch();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute(),
    ])->dispatch();

    expect(fn () => Execution::assertBatchCount(3))
        ->toThrow(ExpectationFailedException::class, 'A batch was pushed [2] times instead of [3] times.');
});
