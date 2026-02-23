<?php

declare(strict_types=1);

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Jobs\ExecutableJob;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\ConfigureByConfigHookExecutable;
use Workbench\App\Executables\Configuration\FullyConfiguredByPropertiesExecutable;
use Workbench\App\Executables\Configuration\ShouldDispatchAfterCommitByInterfaceExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;

beforeEach(function () {
    Queue::fake();
});

it('can be dispatched without specifying after commit', function (): void {
    PlainQueueableExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->afterCommit)->toBeNull();
    });
});

it('can set after commit by property', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->afterCommit)->toBeTrue();
    });
});

it('can set after commit by interface', function () {
    ShouldDispatchAfterCommitByInterfaceExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->afterCommit)->toBeTrue();
    });
});

it('can set after commit by config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->afterCommit());

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->afterCommit)->toBeTrue();
    });
});

it('can set before commit by config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->beforeCommit());

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->afterCommit)->toBeFalse();
    });
});

it('can set after commit on dispatch', function () {
    PlainQueueableExecutable::onQueue()->afterCommit()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->afterCommit)->toBeTrue();
    });
});

it('can set before commit on dispatch', function () {
    PlainQueueableExecutable::onQueue()->beforeCommit()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->afterCommit)->toBeFalse();
    });
});
