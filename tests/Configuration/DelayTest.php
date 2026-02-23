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

it('can be dispatched without specifying delay', function (): void {
    PlainQueueableExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->delay)->toBeNull();
    });
});

it('can set delay on property', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->delay)->toBe(60);
    });
});

it('can set delay by config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->delay(120));

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->delay)->toBe(120);
    });
});

it('can set delay on dispatch', function () {
    PlainQueueableExecutable::onQueue()->delay(30)->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->delay)->toBe(30);
    });
});

it('can unset delay on dispatch', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->delay(null)->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->delay)->toBeNull();
    });
});

it('can set delay on prepared job', function () {
    /** @var ExecutableJob $job */
    $job = PlainQueueableExecutable::prepare()->delay(90)->execute();

    expect($job->delay)->toBe(90);
});
