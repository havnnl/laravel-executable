<?php

declare(strict_types=1);

use Havn\Executable\Testing\Facades\Execution;
use Havn\Executable\Testing\Queueing\PushedBatch;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\PlainQueueableExecutable;

it('fails when bus not faked', function () {
    expect(fn () => Execution::batched())
        ->toThrow(ExpectationFailedException::class, 'Bus was not faked. Use [Bus::fake()].');
});

it('returns all batches when no filter provided', function () {
    Bus::fake();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute('first'),
    ])->name('batch-1')->dispatch();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute('second'),
    ])->name('batch-2')->dispatch();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute('third'),
    ])->name('batch-3')->dispatch();

    $batches = Execution::batched();

    expect($batches)
        ->toHaveCount(3)
        ->each()
        ->toBeInstanceOf(PushedBatch::class);
});

it('returns empty collection when no batches dispatched', function () {
    Bus::fake();

    $batches = Execution::batched();

    expect($batches)->toBeEmpty();
});

it('filters batches', function () {
    Bus::fake();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute('first'),
    ])->name('imports')->dispatch();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute('second'),
    ])->name('exports')->dispatch();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute('third'),
    ])->name('imports')->dispatch();

    $importBatches = Execution::batched(fn (PushedBatch $batch) => $batch->hasName('imports'));

    expect($importBatches)->toHaveCount(2);

    $exportBatches = Execution::batched(fn (PushedBatch $batch) => $batch->hasName('exports'));

    expect($exportBatches)->toHaveCount(1);
});

it('returns empty collection when filter matches nothing', function () {
    Bus::fake();

    Bus::batch([
        PlainQueueableExecutable::prepare()->execute('first'),
    ])->name('batch-1')->dispatch();

    $batches = Execution::batched(fn (PushedBatch $batch) => $batch->hasName('non-existent'));

    expect($batches)->toBeEmpty();
});
