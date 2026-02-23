<?php

declare(strict_types=1);

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Jobs\ExecutableUniqueJob;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\ConfigureByConfigHookExecutable;
use Workbench\App\Executables\Configuration\ShouldBeUniqueByInterfaceExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;

beforeEach(function () {
    Queue::fake();
});

it('can be unique by interface', function () {
    ShouldBeUniqueByInterfaceExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableUniqueJob $job) {
        return expect($job->uniqueId)->toBe(ShouldBeUniqueByInterfaceExecutable::class);
    });
});

it('can be unique by config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->shouldBeUnique());

    Queue::assertPushed(function (ExecutableUniqueJob $job) {
        return expect($job->uniqueId)->toBe(ConfigureByConfigHookExecutable::class);
    });
});

it('can be unique on dispatch', function () {
    PlainQueueableExecutable::onQueue()->shouldBeUnique()->execute();

    Queue::assertPushed(function (ExecutableUniqueJob $job) {
        return expect($job->uniqueId)->toBe(PlainQueueableExecutable::class);
    });
});

it('does not support unique jobs for batches and chains', function () {
    PlainQueueableExecutable::prepare()->shouldBeUnique()->execute();
})->throws(\BadMethodCallException::class);
