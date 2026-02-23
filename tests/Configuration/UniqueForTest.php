<?php

declare(strict_types=1);

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Jobs\ExecutableUniqueJob;
use Havn\Executable\Jobs\ExecutableUniqueUntilProcessingJob;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\ConfigMethodReturnsInputExecutable;
use Workbench\App\Executables\Configuration\ConfigureByConfigHookExecutable;
use Workbench\App\Executables\Configuration\FullyConfiguredByPropertiesExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;

beforeEach(function () {
    Queue::fake();
});

it('can set unique for on property for unique executable', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->shouldBeUnique()->execute();

    Queue::assertPushed(function (ExecutableUniqueJob $job) {
        return expect($job->uniqueFor)->toBe(90);
    });
});

it('can set unique for on property for unique until processing executable', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->shouldBeUniqueUntilProcessing()->execute();

    Queue::assertPushed(function (ExecutableUniqueUntilProcessingJob $job) {
        return expect($job->uniqueFor)->toBe(90);
    });
});

it('can set unique for by config hook for unique executable', function () {
    ConfigureByConfigHookExecutable::onQueue()
        ->shouldBeUnique()
        ->execute(fn (QueueableConfig $config) => $config->uniqueFor(60));

    Queue::assertPushed(function (ExecutableUniqueJob $job) {
        return expect($job->uniqueFor)->toBe(60);
    });
});

it('can set unique for by config hook for unique until processing executable', function () {
    ConfigureByConfigHookExecutable::onQueue()
        ->shouldBeUniqueUntilProcessing()
        ->execute(fn (QueueableConfig $config) => $config->uniqueFor(120));

    Queue::assertPushed(function (ExecutableUniqueUntilProcessingJob $job) {
        return expect($job->uniqueFor)->toBe(120);
    });
});

it('can set unique for on method for unique executable', function () {
    ConfigMethodReturnsInputExecutable::onQueue()
        ->shouldBeUnique()
        ->execute(240);

    Queue::assertPushed(function (ExecutableUniqueJob $job) {
        return expect($job->uniqueFor)->toBe(240);
    });
});

it('can set unique for on method for unique until processing executable', function () {
    ConfigMethodReturnsInputExecutable::onQueue()
        ->shouldBeUniqueUntilProcessing()
        ->execute(300);

    Queue::assertPushed(function (ExecutableUniqueUntilProcessingJob $job) {
        return expect($job->uniqueFor)->toBe(300);
    });
});

it('can set unique for on dispatch for unique executable', function () {
    PlainQueueableExecutable::onQueue()
        ->shouldBeUnique()
        ->withUniqueFor(360)->execute();

    Queue::assertPushed(function (ExecutableUniqueJob $job) {
        return expect($job->uniqueFor)->toBe(360);
    });
});

it('can set unique for on dispatch for unique until processing executable', function () {
    PlainQueueableExecutable::onQueue()
        ->shouldBeUniqueUntilProcessing()
        ->withUniqueFor(420)->execute();

    Queue::assertPushed(function (ExecutableUniqueUntilProcessingJob $job) {
        return expect($job->uniqueFor)->toBe(420);
    });
});

it('does not support unique jobs for batches and chains', function () {
    PlainQueueableExecutable::prepare()->withUniqueFor(480)->execute();
})->throws(\BadMethodCallException::class);
