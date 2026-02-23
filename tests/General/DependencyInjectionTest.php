<?php

declare(strict_types=1);

use Havn\Executable\Jobs\ExecutableJob;
use Havn\Executable\PendingExecution;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\PlainQueueableExecutable;

beforeEach(function () {
    $this->excecutable = app(PlainQueueableExecutable::class);
});

it('executes in sync without PendingExecution after dependency injection', function () {
    expect($this->excecutable)->not()->toBeInstanceOf(PendingExecution::class)
        ->and($this->excecutable->execute('input'))->toBe('input');
});

it('can execute in sync through PendingExecution after dependency injection', function () {
    $pendingExecutable = $this->excecutable->sync();

    expect($pendingExecutable)->toBeInstanceOf(PendingExecution::class)
        ->and($pendingExecutable->execute('input'))->toBe('input');
});

it('can execute on queue after dependency injection', function () {
    Queue::fake();

    $this->excecutable->onQueue('some-queue')->execute('value');

    Queue::assertPushedOn('some-queue', function (ExecutableJob $job) {
        return expect($job->executableClass())->toBe(PlainQueueableExecutable::class)
            ->and($job->arguments())->toBe(['input' => 'value']);
    });
});

it('can execute on chain after dependency injection', function () {
    Bus::fake();

    Bus::chain([
        $this->excecutable->prepare()->execute('value'),
    ])->dispatch();

    Bus::assertChained([
        fn (ExecutableJob $job) => $job->arguments() == ['input' => 'value'],
    ]);
});

it('can execute on batch after dependency injection', function () {
    Bus::fake();

    Bus::batch([
        $this->excecutable->prepare()->execute('value'),
    ])->dispatch();

    Bus::assertBatched(function (PendingBatch $batch) {
        /** @var ExecutableJob[] $jobs */
        return expect($jobs = $batch->jobs)
            ->toHaveCount(1)
            ->each()->toBeInstanceOf(ExecutableJob::class)
            ->and($jobs[0]->arguments())->toBe(['input' => 'value']);
    });
});
