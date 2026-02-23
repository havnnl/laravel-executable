<?php

declare(strict_types=1);

use Carbon\Carbon;
use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Jobs\ExecutableJob;
use Havn\Executable\Jobs\ExecutableUniqueJob;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\ConfigHookOverridesPropertyExecutable;
use Workbench\App\Executables\Configuration\DispatchOverridesAllConfigExecutable;
use Workbench\App\Executables\Configuration\InterfaceAndAttributeOverridePropertyExecutable;
use Workbench\App\Executables\Configuration\MethodOverridesConfigHookExecutable;

beforeEach(function () {
    Queue::fake();

    Carbon::setTestNow(now());
});

it('gives priority to interfaces and attributes over properties', function () {
    InterfaceAndAttributeOverridePropertyExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->afterCommit)->toBeTrue() // Interface wins over property (false)
            ->and($job->shouldBeEncrypted)->toBeTrue() // Interface wins over property (false)
            ->and($job->shouldBeEncrypted)->toBeTrue() // Interface wins over property (false)
            ->and($job->withoutRelations)->toBeTrue(); // Attribute wins over property (false)
    });
});

it('gives priority on configure hook over properties', function () {
    ConfigHookOverridesPropertyExecutable::onQueue()->shouldBeUnique()
        ->execute(fn (QueueableConfig $config) => $config->beforeCommit()
            ->deleteWhenMissingModels(false)
            ->failOnTimeout(false)
            ->backoff(1)
            ->delay(2)
            ->maxExceptions(3)
            ->retryUntil(now()->addMinutes(4))
            ->shouldBeEncrypted(false)
            ->timeout(5)
            ->tries(6)
            ->uniqueFor(7)
            ->uniqueId(8)
            ->onconnection('config-connection')
            ->onqueue('config-queue'));

    Queue::assertPushed(function (ExecutableUniqueJob $job) {
        return expect($job->afterCommit)->toBeFalse()
            ->and($job->deleteWhenMissingModels)->toBeFalse()
            ->and($job->failOnTimeout)->toBeFalse()
            ->and($job->backoff)->toBe(1)
            ->and($job->delay)->toBe(2)
            ->and($job->maxExceptions)->toBe(3)
            ->and($job->retryUntil)->toEqual(now()->addMinutes(4))
            ->and($job->shouldBeEncrypted)->toBeFalse()
            ->and($job->timeout)->toBe(5)
            ->and($job->tries)->toBe(6)
            ->and($job->uniqueFor)->toBe(7)
            ->and($job->uniqueId)->toBe(ConfigHookOverridesPropertyExecutable::class.':8')
            ->and($job->connection)->toBe('config-connection')
            ->and($job->queue)->toBe('config-queue');
    });
});

it('gives priority to methods over configure', function () {
    MethodOverridesConfigHookExecutable::onQueue()->shouldBeUnique()
        ->execute(fn (QueueableConfig $config) => $config
            ->backoff(1)
            ->displayName('config-display-name')
            ->retryUntil(now()->addMinutes(5))
            ->tries(2)
            ->uniqueFor(3)
            ->uniqueId(4));

    Queue::assertPushed(function (ExecutableUniqueJob $job) {
        return expect($job->backoff)->toBe(10)
            ->and($job->retryUntil)->toEqual(now()->addMinutes(10))
            ->and($job->tries)->toBe(20)
            ->and($job->uniqueFor)->toBe(30)
            ->and($job->uniqueId)->toBe(MethodOverridesConfigHookExecutable::class.':40');
    });
});

it('gives priority to dispatch over methods', function () {
    DispatchOverridesAllConfigExecutable::onQueue()
        ->afterCommit()
        ->withBackoff(10)
        ->deleteWhenMissingModels(true)
        ->withDisplayName('dispatch-display-name')
        ->failOnTimeout(false)
        ->shouldBeEncrypted(false)
        ->maxExceptions(30)
        ->onConnection('dispatch-connection')
        ->onQueue('dispatch-queue')
        ->shouldRetryUntil(now()->addMinutes(40))
        ->timeout(50)
        ->withTries(60)
        ->shouldBeUnique()
        ->withUniqueFor(70)
        ->withUniqueId(80)
        ->withoutDelay()
        ->execute(fn (QueueableConfig $config) => $config->beforeCommit()
            ->deleteWhenMissingModels(false)
            ->failOnTimeout(false)
            ->backoff(1)
            ->delay(2)
            ->maxExceptions(3)
            ->retryUntil(now()->addMinutes(4))
            ->shouldBeEncrypted(false)
            ->timeout(5)
            ->tries(6)
            ->uniqueFor(7)
            ->uniqueId(8)
            ->onconnection('config-connection')
            ->onqueue('config-queue'));

    Queue::assertPushed(function (ExecutableUniqueJob $job) {
        return expect($job->afterCommit)->toBeTrue()
            ->and($job->backoff)->toBe(10)
            ->and($job->deleteWhenMissingModels)->toBeTrue()
            ->and($job->displayName)->toBe('dispatch-display-name')
            ->and($job->failOnTimeout)->toBeFalse()
            ->and($job->shouldBeEncrypted)->toBeFalse()
            ->and($job->maxExceptions)->toBe(30)
            ->and($job->connection)->toBe('dispatch-connection')
            ->and($job->queue)->toBe('dispatch-queue')
            ->and($job->retryUntil)->toEqual(now()->addMinutes(40))
            ->and($job->timeout)->toBe(50)
            ->and($job->tries)->toBe(60)
            ->and($job->uniqueFor)->toBe(70)
            ->and($job->uniqueId)->toBe(DispatchOverridesAllConfigExecutable::class.':80')
            ->and($job->delay)->toBeNull();
    });
});
