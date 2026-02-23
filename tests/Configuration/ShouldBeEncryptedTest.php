<?php

declare(strict_types=1);

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Jobs\ExecutableJob;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\ConfigureByConfigHookExecutable;
use Workbench\App\Executables\Configuration\EncryptedByInterfaceExecutable;
use Workbench\App\Executables\Configuration\FullyConfiguredByPropertiesExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;

beforeEach(function () {
    Queue::fake();
});

it('can be dispatched without encryption by default', function (): void {
    PlainQueueableExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->shouldBeEncrypted)->toBeNull();
    });
});

it('can set encryption by interface', function () {
    EncryptedByInterfaceExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->shouldBeEncrypted)->toBeTrue();
    });
});

it('can set encryption on property', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->shouldBeEncrypted)->toBeTrue();
    });
});

it('can set encryption by config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->shouldBeEncrypted());

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->shouldBeEncrypted)->toBeTrue();
    });
});

it('can set encryption on dispatch', function () {
    PlainQueueableExecutable::onQueue()->shouldBeEncrypted()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->shouldBeEncrypted)->toBeTrue();
    });
});

it('can disable encryption on dispatch', function () {
    EncryptedByInterfaceExecutable::onQueue()->shouldBeEncrypted(false)->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->shouldBeEncrypted)->toBeFalse();
    });
});

it('can set encryption on prepared job', function () {
    /** @var ExecutableJob $job */
    $job = PlainQueueableExecutable::prepare()->shouldBeEncrypted()->execute();

    expect($job->shouldBeEncrypted)->toBeTrue();
});
