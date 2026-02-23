<?php

declare(strict_types=1);

use Havn\Executable\Jobs\ExecutableUniqueJob;
use Havn\Executable\Jobs\ExecutableUniqueUntilProcessingJob;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\ConfigMethodReturnsInputExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;

beforeEach(function () {
    Queue::fake();
});

it('has default unique via for unique executable', function () {
    PlainQueueableExecutable::onQueue()
        ->shouldBeUnique()
        ->execute();

    Queue::assertPushed(function (ExecutableUniqueJob $job) {
        return expect($job->uniqueVia())->toBe(resolve(Repository::class));
    });
});

it('can set unique via on method for unique executable', function () {
    $cache = clone resolve(Repository::class);

    ConfigMethodReturnsInputExecutable::onQueue()
        ->shouldBeUnique()
        ->execute($cache);

    Queue::assertPushed(function (ExecutableUniqueJob $job) use ($cache) {
        return expect($job->uniqueVia())->toBe($cache);
    });
});

it('has default unique via for unique until processing executable', function () {
    PlainQueueableExecutable::onQueue()
        ->shouldBeUniqueUntilProcessing()
        ->execute();

    Queue::assertPushed(function (ExecutableUniqueUntilProcessingJob $job) {
        return expect($job->uniqueVia())->toBe(resolve(Repository::class));
    });
});

it('can set unique via on method for unique until processing executable', function () {
    $cache = clone resolve(Repository::class);
    ConfigMethodReturnsInputExecutable::onQueue()
        ->shouldBeUniqueUntilProcessing()
        ->execute($cache);

    Queue::assertPushed(function (ExecutableUniqueUntilProcessingJob $job) use ($cache) {
        return expect($job->uniqueVia())->toBe($cache);
    });
});
