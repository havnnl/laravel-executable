<?php

declare(strict_types=1);

use Havn\Executable\Testing\Facades\Execution;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\PlainQueueableExecutable;

it('fails when bus not faked', function () {
    expect(fn () => Execution::assertNothingBatched([]))
        ->toThrow(ExpectationFailedException::class, 'Bus was not faked. Use [Bus::fake()].');
});

it('passes when nothing batched', function () {
    Bus::fake();

    Execution::assertNothingBatched();
});

it('fails when something is batched', function () {
    Bus::fake();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute(),
    ])->dispatch();

    expect(fn () => Execution::assertNothingBatched())
        ->toThrow(ExpectationFailedException::class, 'jobs were dispatched unexpectedly');
});
