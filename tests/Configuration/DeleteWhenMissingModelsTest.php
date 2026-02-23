<?php

declare(strict_types=1);

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Jobs\ExecutableJob;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\ConfigureByConfigHookExecutable;
use Workbench\App\Executables\Configuration\DeleteWhenMissingModelsByAttributeExecutable;
use Workbench\App\Executables\Configuration\FullyConfiguredByPropertiesExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;

beforeEach(function (): void {
    Queue::fake();
});

it('can be dispatched without specifying delete when missing models', function (): void {
    PlainQueueableExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->deleteWhenMissingModels)->toBeNull();
    });
});

it('can set delete when missing models by property', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->deleteWhenMissingModels)->toBeTrue();
    });
});

it('can set delete when missing models by attribute', function (): void {
    DeleteWhenMissingModelsByAttributeExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->deleteWhenMissingModels)->toBeTrue();
    });
});

it('can set delete when missing models by config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->deleteWhenMissingModels());

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->deleteWhenMissingModels)->toBeTrue();
    });
});

it('can set delete when missing models on dispatch', function () {
    PlainQueueableExecutable::onQueue()->deleteWhenMissingModels()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->deleteWhenMissingModels)->toBeTrue();
    });
});

it('can set delete when missing models on prepared job', function () {
    /** @var ExecutableJob $job */
    $job = PlainQueueableExecutable::prepare()->deleteWhenMissingModels()->execute();

    expect($job->deleteWhenMissingModels)->toBeTrue();
});
