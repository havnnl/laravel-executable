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

it('can be dispatched without specifying tries', function (): void {
    PlainQueueableExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->tries)->toBeNull();
    });
});

it('can set tries by property', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->tries)->toBe(10);
    });
});

it('can set tries by config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->tries(15));

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->tries)->toBe(15);
    });
});

it('can set tries on method', function () {
    ConfigMethodReturnsInputExecutable::onQueue()->execute(20);

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->tries)->toBe(20);
    });
});

it('can set tries on dispatch', function () {
    PlainQueueableExecutable::onQueue()->withTries(50)->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->tries)->toBe(50);
    });
});

it('can set tries on prepared job', function () {
    /** @var ExecutableJob $job */
    $job = PlainQueueableExecutable::prepare()->withTries(75)->execute();

    expect($job->tries)->toBe(75);
});
