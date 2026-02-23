<?php

declare(strict_types=1);

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Jobs\ExecutableJob;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\ConfigMethodReturnsInputExecutable;
use Workbench\App\Executables\Configuration\ConfigureByConfigHookExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;

beforeEach(function () {
    Queue::fake();
});

it('has class name as default display name', function () {
    PlainQueueableExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->displayName())->toBe(PlainQueueableExecutable::class);
    });
});

it('can set display name by config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->displayName('name by config hook'));

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->displayName())->toBe('name by config hook');
    });
});

it('can set display name on method', function () {
    ConfigMethodReturnsInputExecutable::onQueue()->execute('name by method');

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->displayName())->toBe('name by method');
    });
});

it('can set display name on dispatch', function () {
    PlainQueueableExecutable::onQueue()->withDisplayName('name on dispatch')->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->displayName())->toBe('name on dispatch');
    });
});

it('can set display name on prepared job', function () {
    /** @var ExecutableJob $job */
    $job = PlainQueueableExecutable::prepare()->withDisplayName('name on dispatch')->execute();

    expect($job->displayName())->toBe('name on dispatch');
});
