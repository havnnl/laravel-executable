<?php

declare(strict_types=1);

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Jobs\ExecutableJob;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\ConfigMethodReturnsInputExecutable;
use Workbench\App\Executables\Configuration\ConfigureByConfigHookExecutable;
use Workbench\App\Executables\Configuration\FullyConfiguredByPropertiesExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;

beforeEach(function () {
    Queue::fake();
});

it('has no backoff by default', function (): void {
    PlainQueueableExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->backoff)->toBeNull();
    });
});

it('can set backoff by property', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->backoff)->toBe(5);
    });
});

it('can set backoff by config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->backoff([5, 10]));

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->backoff)->toBe([5, 10]);
    });
});

it('can set backoff by method', function () {
    ConfigMethodReturnsInputExecutable::onQueue()->execute(60);

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->backoff)->toBe(60);
    });
});

it('can set backoff on dispatch', function () {
    PlainQueueableExecutable::onQueue()->withBackoff([10, 20])->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->backoff)->toBe([10, 20]);
    });
});

it('can set backoff on prepared job', function () {
    /** @var ExecutableJob $job */
    $job = PlainQueueableExecutable::prepare()->withBackoff(90)->execute();

    expect($job->backoff)->toBe(90);
});
