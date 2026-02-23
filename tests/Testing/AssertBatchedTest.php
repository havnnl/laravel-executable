<?php

declare(strict_types=1);

use Havn\Executable\Testing\Facades\Execution;
use Havn\Executable\Testing\Queueing\PushedBatch;
use Havn\Executable\Testing\Queueing\PushedJob;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\PlainQueueableExecutable;
use Workbench\App\Executables\UseTransactionExecutable;

it('fails when bus not faked', function () {
    expect(fn () => Execution::assertBatched([]))
        ->toThrow(ExpectationFailedException::class, 'Bus was not faked. Use [Bus::fake()].');
});

it('passes when using class names', function () {
    Bus::fake();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute('first'),
        UseTransactionExecutable::prepare()->execute('second'),
        PlainQueueableExecutable::prepare()->execute('third'),
    ])->dispatch();

    Execution::assertBatched([
        PlainQueueableExecutable::class,
        PlainQueueableExecutable::class,
        UseTransactionExecutable::class,
    ]);

    Execution::assertBatched([
        PlainQueueableExecutable::class,
        UseTransactionExecutable::class,
        PlainQueueableExecutable::class,
    ]);
});

it('fails when using class names', function () {
    Bus::fake();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute('first'),
        UseTransactionExecutable::prepare()->execute('second'),
        PlainQueueableExecutable::prepare()->execute('third'),
    ])->dispatch();

    expect(fn () => Execution::assertBatched([
        PlainQueueableExecutable::class,
        UseTransactionExecutable::class,
    ]))
        ->toThrow(ExpectationFailedException::class, 'The expected batch was not dispatched.');
});

it('passes when using array with closures', function () {
    Bus::fake();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute('first'),
        UseTransactionExecutable::prepare()->execute('second'),
        PlainQueueableExecutable::prepare()->execute('third'),
    ])->dispatch();

    Execution::assertBatched([
        fn (PushedJob $job) => $job->executedWith('first'),
        fn (PushedJob $job) => $job->executedWith('second'),
        fn (PushedJob $job) => $job->executedWith('third'),
    ]);

    Execution::assertBatched([
        fn (PushedJob $job) => $job->executedWith('first'),
        fn (PushedJob $job) => $job->executedWith('third'),
        fn (PushedJob $job) => $job->executedWith('second'),
    ]);
});

it('fails when using array with closures', function () {
    Bus::fake();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute('first'),
        UseTransactionExecutable::prepare()->execute('second'),
        PlainQueueableExecutable::prepare()->execute('third'),
    ])->dispatch();

    expect(fn () => Execution::assertBatched([
        fn (PushedJob $job) => $job->executedWith('first'),
        fn (PushedJob $job) => $job->executedWith('second'),
        fn (PushedJob $job) => $job->executedWith('mismatch'),
    ]))
        ->toThrow(ExpectationFailedException::class, 'The expected batch was not dispatched.');
});

it('passes when using closure', function () {
    Bus::fake();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute('first'),
        UseTransactionExecutable::prepare()->execute('second'),
        PlainQueueableExecutable::prepare()->execute('third'),
    ])->dispatch();

    Execution::assertBatched(function (PushedBatch $batch) {
        return $batch->containsExactly([
            fn (PushedJob $job) => $job->executedWith('first'),
            fn (PushedJob $job) => $job->executedWith('second'),
            fn (PushedJob $job) => $job->executedWith('third'),
        ]);
    });
});

it('fails when using closure', function () {
    Bus::fake();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute('first'),
        UseTransactionExecutable::prepare()->execute('second'),
        PlainQueueableExecutable::prepare()->execute('third'),
    ])->dispatch();

    expect(
        function () {
            Execution::assertBatched(function (PushedBatch $batch) {
                return $batch->containsExactly([
                    fn (PushedJob $job) => $job->executedWith('first'),
                    fn (PushedJob $job) => $job->executedWith('second'),
                    fn (PushedJob $job) => $job->executedWith('mismatch'),
                ]);
            });
        })
        ->toThrow(ExpectationFailedException::class, 'The expected batch was not dispatched.');
});
