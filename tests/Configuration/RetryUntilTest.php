<?php

declare(strict_types=1);

use Carbon\Carbon;
use Havn\Executable\Jobs\ExecutableJob;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\ConfigMethodReturnsInputExecutable;
use Workbench\App\Executables\Configuration\FullyConfiguredByPropertiesExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;

beforeEach(function () {
    Queue::fake();

    Carbon::setTestNow(now());
});

it('can be dispatched without specifying retry until', function (): void {
    PlainQueueableExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->retryUntil)->toBeNull();
    });
});

it('can set retry until on property', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->retryUntil)->toBe(1767225599);
    });
});

it('can set retry until on method', function () {
    ConfigMethodReturnsInputExecutable::onQueue()->execute(now()->addMinutes(10));

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->retryUntil)->toEqual(now()->addMinutes(10));
    });
});

it('can set retry until on dispatch', function () {
    FullyConfiguredByPropertiesExecutable::onQueue()->shouldRetryUntil(now()->addMinutes(10))->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->retryUntil)->toEqual(now()->addMinutes(10));
    });
});

it('can set retry until on prepared job', function () {
    /** @var ExecutableJob $job */
    $job = FullyConfiguredByPropertiesExecutable::prepare()->shouldRetryUntil(now()->addMinutes(10))->execute();

    expect($job->retryUntil)->toEqual(now()->addMinutes(10));
});
