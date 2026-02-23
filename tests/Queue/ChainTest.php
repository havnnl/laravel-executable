<?php

declare(strict_types=1);

use Havn\Executable\Jobs\ExecutableJob;
use Illuminate\Support\Facades\Bus;
use Workbench\App\Executables\PlainQueueableExecutable;

beforeEach(function () {
    Bus::fake();
});

it('chains prepared executables', function () {
    Bus::chain([
        PlainQueueableExecutable::prepare()->execute('first'),
        PlainQueueableExecutable::prepare()->execute('second'),
        PlainQueueableExecutable::prepare()->execute('third'),
    ])
        ->dispatch();

    Bus::assertChained([
        fn (ExecutableJob $job) => $job->arguments() == ['input' => 'first'],
        fn (ExecutableJob $job) => $job->arguments() == ['input' => 'second'],
        fn (ExecutableJob $job) => $job->arguments() == ['input' => 'third'],
    ]);
});
