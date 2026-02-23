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

it('can set connection on property', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->connection)->toBe('property-connection');
    });
});

it('can set connection by config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->onConnection('connection-by-config-hook'));

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->connection)->toBe('connection-by-config-hook');
    });
});

it('can set connection on dispatch', function () {
    PlainQueueableExecutable::onQueue()->onConnection('dispatch-connection')->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->connection)->toBe('dispatch-connection')
            ->and($job->chainConnection)->toBeNull();
    });
});

it('can set all connections on dispatch', function () {
    PlainQueueableExecutable::onQueue()
        ->allOnConnection('dispatch-connection')
        ->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->connection)->toBe('dispatch-connection')
            ->and($job->chainConnection)->toBe('dispatch-connection');
    });
});

it('can set connection on prepared job', function () {
    /** @var ExecutableJob $job */
    $job = PlainQueueableExecutable::prepare()->onConnection('prepare-connection')->execute();

    expect($job->connection)->toBe('prepare-connection');
});
