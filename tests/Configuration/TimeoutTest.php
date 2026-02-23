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

it('can be dispatched without specifying timeout', function (): void {
    PlainQueueableExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->timeout)->toBeNull();
    });
});

it('can set timeout by property', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->timeout)->toBe(120);
    });
});

it('can set timeout by config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->timeout(240));

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->timeout)->toBe(240);
    });
});

it('can set timeout on dispatch', function () {
    PlainQueueableExecutable::onQueue()->timeout(600)->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->timeout)->toBe(600);
    });
});

it('can set timeout on prepared job', function () {
    /** @var ExecutableJob $job */
    $job = PlainQueueableExecutable::prepare()->timeout(900)->execute();

    expect($job->timeout)->toBe(900);
});
