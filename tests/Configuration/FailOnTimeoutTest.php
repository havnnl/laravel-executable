<?php

declare(strict_types=1);

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Jobs\ExecutableJob;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\ConfigureByConfigHookExecutable;
use Workbench\App\Executables\Configuration\FullyConfiguredByPropertiesExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;

beforeEach(function () {
    Queue::fake();
});

it('can be dispatched without specifying fail on timeout', function (): void {
    PlainQueueableExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->failOnTimeout)->toBeNull();
    });
});

it('can set fail on timeout by property', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->failOnTimeout)->toBeTrue();
    });
});

it('can set fail on timeout by config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->failOnTimeout());

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->failOnTimeout)->toBeTrue();
    });
});

it('can set fail on timeout on dispatch', function () {
    PlainQueueableExecutable::onQueue()->failOnTimeout()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->failOnTimeout)->toBeTrue();
    });
});

it('can disable fail on timeout on dispatch', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->failOnTimeout(false)->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->failOnTimeout)->toBeFalse();
    });
});

it('can set fail on timeout on prepared job', function () {
    /** @var ExecutableJob $job */
    $job = PlainQueueableExecutable::prepare()->failOnTimeout()->execute();

    expect($job->failOnTimeout)->toBeTrue();
});
