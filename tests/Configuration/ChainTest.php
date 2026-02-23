<?php

declare(strict_types=1);

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Jobs\ExecutableJob;
use Havn\Executable\Jobs\ExecutableUniqueJob;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\ConfigureByConfigHookExecutable;
use Workbench\App\Executables\Configuration\ShouldBeUniqueByInterfaceExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;
use Workbench\App\Jobs\SimpleJob;

beforeEach(function () {
    Queue::fake();
});

it('can set chain on config hook', function () {
    ConfigureByConfigHookExecutable::onQueue()->execute(fn (QueueableConfig $config) => $config->chain([
        new SimpleJob,
        PlainQueueableExecutable::prepare()->execute(),
        ShouldBeUniqueByInterfaceExecutable::prepare()->execute(),
    ]));

    Queue::assertPushed(function (ExecutableJob $job) {
        expect($job->executableClass())->toBe(ConfigureByConfigHookExecutable::class)
            ->and($job->chained)->toHaveCount(3);

        $chainedJob1 = unserialize($job->chained[0]);
        /** @var ExecutableJob $chainedJob2 */
        $chainedJob2 = unserialize($job->chained[1]);
        /** @var ExecutableUniqueJob $chainedJob3 */
        $chainedJob3 = unserialize($job->chained[2]);

        expect($chainedJob1)->toBeInstanceOf(SimpleJob::class);

        expect($chainedJob2)->toBeInstanceOf(ExecutableJob::class)
            ->and($chainedJob2->executableClass())->toBe(PlainQueueableExecutable::class);

        expect($chainedJob3)->toBeInstanceOf(ExecutableUniqueJob::class)
            ->and($chainedJob3->executableClass())->toBe(ShouldBeUniqueByInterfaceExecutable::class);

        return true;
    });
});

it('can set chain on dispatch', function () {
    PlainQueueableExecutable::onQueue()
        ->chain([
            new SimpleJob,
            PlainQueueableExecutable::prepare()->execute(),
            ShouldBeUniqueByInterfaceExecutable::prepare()->execute(),
        ])
        ->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        expect($job->executableClass())->toBe(PlainQueueableExecutable::class)
            ->and($job->chained)->toHaveCount(3);

        $chainedJob1 = unserialize($job->chained[0]);
        /** @var ExecutableJob $chainedJob2 */
        $chainedJob2 = unserialize($job->chained[1]);
        /** @var ExecutableUniqueJob $chainedJob3 */
        $chainedJob3 = unserialize($job->chained[2]);

        expect($chainedJob1)->toBeInstanceOf(SimpleJob::class);

        expect($chainedJob2)->toBeInstanceOf(ExecutableJob::class)
            ->and($chainedJob2->executableClass())->toBe(PlainQueueableExecutable::class);

        expect($chainedJob3)->toBeInstanceOf(ExecutableUniqueJob::class)
            ->and($chainedJob3->executableClass())->toBe(ShouldBeUniqueByInterfaceExecutable::class);

        return true;
    });
});

it('can set nested chains', function () {
    PlainQueueableExecutable::onQueue()
        ->chain([
            PlainQueueableExecutable::prepare()
                ->chain([
                    new SimpleJob,
                    ShouldBeUniqueByInterfaceExecutable::prepare()->execute(),
                ])
                ->execute(),
        ])
        ->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        expect($job->executableClass())->toBe(PlainQueueableExecutable::class)
            ->and($job->chained)->toHaveCount(1);

        $chainedJob = unserialize($job->chained[0]);

        expect($chainedJob)->toBeInstanceOf(ExecutableJob::class)
            ->and($chainedJob->chained)->toHaveCount(2);

        $nestedJob1 = unserialize($chainedJob->chained[0]);
        $nestedJob2 = unserialize($chainedJob->chained[1]);

        expect($nestedJob1)->toBeInstanceOf(SimpleJob::class)
            ->and($nestedJob2)->toBeInstanceOf(ExecutableUniqueJob::class);

        return true;
    });
});
