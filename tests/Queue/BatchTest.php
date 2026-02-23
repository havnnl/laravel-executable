<?php

declare(strict_types=1);

use Havn\Executable\Jobs\ExecutableJob;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Workbench\App\Executables\PlainQueueableExecutable;

beforeEach(function () {
    Bus::fake();
});

it('batches prepared executables', function () {
    Bus::batch([
        PlainQueueableExecutable::prepare()->execute('first'),
        PlainQueueableExecutable::prepare()->execute('second'),
        PlainQueueableExecutable::prepare()->execute('third'),
    ])
        ->dispatch();

    Bus::assertBatched(function (PendingBatch $batch) {
        /** @var ExecutableJob[] $jobs */
        return expect($jobs = $batch->jobs)
            ->toHaveCount(3)
            ->each()->toBeInstanceOf(ExecutableJob::class)
            ->and($jobs[0]->executableClass())->toBe(PlainQueueableExecutable::class)
            ->and($jobs[0]->arguments())->toBe(['input' => 'first'])
            ->and($jobs[1]->executableClass())->toBe(PlainQueueableExecutable::class)
            ->and($jobs[1]->arguments())->toBe(['input' => 'second'])
            ->and($jobs[2]->executableClass())->toBe(PlainQueueableExecutable::class)
            ->and($jobs[2]->arguments())->toBe(['input' => 'third']);
    });
});
