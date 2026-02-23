<?php

declare(strict_types=1);

use Havn\Executable\Jobs\ExecutableSyncJob;
use Illuminate\Support\Facades\Bus;
use Workbench\App\Executables\InputConcatenatingExecutable;

it('can execute after response', function () {
    Bus::fake();

    $returnValue = InputConcatenatingExecutable::sync()->afterResponse()->execute('hello', 'world');

    expect($returnValue)->toBeNull();

    Bus::assertDispatchedAfterResponse(function (ExecutableSyncJob $job) {
        return expect($job->executableClass())->toBe(InputConcatenatingExecutable::class)
            ->and($job->arguments())->toBe(['hello', 'world']);
    });
});
