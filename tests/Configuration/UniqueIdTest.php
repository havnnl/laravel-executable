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

it('can set unique id on property for unique executable', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->shouldBeUnique()->execute();

    Queue::assertPushed(function (ExecutableUniqueJob $job) {
        return expect($job->uniqueId)->toBe(FullyConfiguredByPropertiesExecutable::class.':100');
    });
});

it('can set unique id on property for unique until processing executable', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->shouldBeUniqueUntilProcessing()->execute();

    Queue::assertPushed(function (ExecutableUniqueUntilProcessingJob $job) {
        return expect($job->uniqueId)->toBe(FullyConfiguredByPropertiesExecutable::class.':100');
    });
});

it('can set unique id by config hook for unique executable', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->shouldBeUnique()->uniqueId(101));

    Queue::assertPushed(function (ExecutableUniqueJob $job) {
        return expect($job->uniqueId)->toBe(ConfigureByConfigHookExecutable::class.':101');
    });
});

it('can set unique id by config hook for unique until processing executable', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->shouldBeUniqueUntilProcessing()->uniqueId(102));

    Queue::assertPushed(function (ExecutableUniqueUntilProcessingJob $job) {
        return expect($job->uniqueId)->toBe(ConfigureByConfigHookExecutable::class.':102');
    });
});

it('can set unique id on method for unique executable', function () {
    ConfigMethodReturnsInputExecutable::onQueue()->shouldBeUnique()->execute(103);

    Queue::assertPushed(function (ExecutableUniqueJob $job) {
        return expect($job->uniqueId)->toBe(ConfigMethodReturnsInputExecutable::class.':103');
    });
});

it('can set unique id on method for unique until processing executable', function () {
    ConfigMethodReturnsInputExecutable::onQueue()->shouldBeUniqueUntilProcessing()->execute(104);

    Queue::assertPushed(function (ExecutableUniqueUntilProcessingJob $job) {
        return expect($job->uniqueId)->toBe(ConfigMethodReturnsInputExecutable::class.':104');
    });
});

it('can set unique id on dispatch for unique executable', function () {
    PlainQueueableExecutable::onQueue()->shouldBeUnique()->withUniqueId(105)->execute();

    Queue::assertPushed(function (ExecutableUniqueJob $job) {
        return expect($job->uniqueId)->toBe(PlainQueueableExecutable::class.':105');
    });
});

it('can set unique id on dispatch for unique until processing executable', function () {
    PlainQueueableExecutable::onQueue()->shouldBeUniqueUntilProcessing()->withUniqueId(106)->execute();

    Queue::assertPushed(function (ExecutableUniqueUntilProcessingJob $job) {
        return expect($job->uniqueId)->toBe(PlainQueueableExecutable::class.':106');
    });
});

it('does not support unique jobs for batches and chains', function () {
    PlainQueueableExecutable::prepare()->withUniqueId(107)->execute();
})->throws(\BadMethodCallException::class);
