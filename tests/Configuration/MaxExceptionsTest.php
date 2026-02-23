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

it('can be dispatched without specifying max exceptions', function (): void {
    PlainQueueableExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->maxExceptions)->toBeNull();
    });
});

it('can set max exceptions by property', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->maxExceptions)->toBe(3);
    });
});

it('can set max exceptions by config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->maxExceptions(5));

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->maxExceptions)->toBe(5);
    });
});

it('can set max exceptions on dispatch', function () {
    PlainQueueableExecutable::onQueue()->maxExceptions(8)->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->maxExceptions)->toBe(8);
    });
});

it('can set max exceptions on prepared job', function () {
    /** @var ExecutableJob $job */
    $job = PlainQueueableExecutable::prepare()->maxExceptions(15)->execute();

    expect($job->maxExceptions)->toBe(15);
});
