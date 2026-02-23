<?php

declare(strict_types=1);

use Havn\Executable\Jobs\ExecutableJob;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\FullyConfiguredByPropertiesExecutable;

beforeEach(function () {
    Queue::fake();
});

it('can remove delay on dispatch', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->withoutDelay()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->delay)->toBeNull();
    });
});

it('can remove delay on prepared job', function () {
    /** @var ExecutableJob $job */
    $job = FullyConfiguredByPropertiesExecutable::prepare()->withoutDelay()->execute();

    expect($job->delay)->toBeNull();
});
