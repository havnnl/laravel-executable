<?php

declare(strict_types=1);

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Jobs\ExecutableUniqueUntilProcessingJob;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\ConfigureByConfigHookExecutable;
use Workbench\App\Executables\Configuration\ShouldBeUniqueUntilProcessingByInterfaceExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;

beforeEach(function () {
    Queue::fake();
});

it('can be unique until processing by interface', function () {
    ShouldBeUniqueUntilProcessingByInterfaceExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableUniqueUntilProcessingJob $job) {
        return expect($job->uniqueId)->toBe(ShouldBeUniqueUntilProcessingByInterfaceExecutable::class);
    });
});

it('can be unique until processing by config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->shouldBeUniqueUntilProcessing());

    Queue::assertPushed(function (ExecutableUniqueUntilProcessingJob $job) {
        return expect($job->uniqueId)->toBe(ConfigureByConfigHookExecutable::class);
    });
});

it('can be unique until processing on dispatch', function () {
    PlainQueueableExecutable::onQueue()->shouldBeUniqueUntilProcessing()->execute();

    Queue::assertPushed(function (ExecutableUniqueUntilProcessingJob $job) {
        return expect($job->uniqueId)->toBe(PlainQueueableExecutable::class);
    });
});

it('does not support unique jobs for batches and chains', function () {
    PlainQueueableExecutable::prepare()->shouldBeUniqueUntilProcessing()->execute();
})->throws(\BadMethodCallException::class);
