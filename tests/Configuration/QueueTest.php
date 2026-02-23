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

it('can set queue on property', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->queue)->toBe('property-queue');
    });
});

it('can set queue by config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->onQueue('queue-by-config-hook'));

    Queue::assertPushed(function (ExecutableJob $job) {
        return $job->executableClass() == ConfigureByConfigHookExecutable::class
            && $job->queue === 'queue-by-config-hook';
    });
});

it('can set all queues on dispatch', function () {
    PlainQueueableExecutable::onQueue()
        ->allOnQueue('dispatch-queue')
        ->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->queue)->toBe('dispatch-queue')
            ->and($job->chainQueue)->toBe('dispatch-queue');
    });
});

it('can set queue on prepared job', function () {
    /** @var ExecutableJob $job */
    $job = PlainQueueableExecutable::prepare()->onQueue('prepare-queue')->execute();

    expect($job->queue)->toBe('prepare-queue');
});
