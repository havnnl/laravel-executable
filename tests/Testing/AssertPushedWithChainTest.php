<?php

declare(strict_types=1);

use Havn\Executable\Testing\Queueing\PushedJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\Configuration\ShouldBeUniqueByInterfaceExecutable;
use Workbench\App\Executables\PlainQueueableExecutable;
use Workbench\App\Jobs\SimpleJob;

it('fails when queue is not faked', function () {
    expect(fn () => PlainQueueableExecutable::assert()->queued()->withChain([]))
        ->toThrow(ExpectationFailedException::class, 'Queue was not faked. Use [Queue::fake()]');
});

it('passes if executable assertion and jobs by class name match', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()
        ->chain([
            new SimpleJob,
            PlainQueueableExecutable::prepare()->execute(),
            ShouldBeUniqueByInterfaceExecutable::prepare()->execute(),
        ])
        ->execute('input');

    PlainQueueableExecutable::assert()->queued()
        ->where(fn (PushedJob $job) => true)
        ->withChain([
            SimpleJob::class,
            PlainQueueableExecutable::class,
            ShouldBeUniqueByInterfaceExecutable::class,
        ]);
});

it('fails if executable assertion and jobs by class name match', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()
        ->chain([
            new SimpleJob,
            PlainQueueableExecutable::prepare()->execute(),
            ShouldBeUniqueByInterfaceExecutable::prepare()->execute(),
        ])
        ->execute('input');

    $assertion = PlainQueueableExecutable::assert()->queued()
        ->where(fn (PushedJob $job) => false)
        ->withChain([
            SimpleJob::class,
            PlainQueueableExecutable::class,
            ShouldBeUniqueByInterfaceExecutable::class,
        ]);

    expect(fn () => $assertion->__destruct())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was not queued with chain matching custom filters.');
});

it('passes if jobs by class name match', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()
        ->chain([
            new SimpleJob,
            PlainQueueableExecutable::prepare()->execute(),
            ShouldBeUniqueByInterfaceExecutable::prepare()->execute(),
        ])
        ->execute('input');

    PlainQueueableExecutable::assert()->queued()->withChain([
        SimpleJob::class,
        PlainQueueableExecutable::class,
        ShouldBeUniqueByInterfaceExecutable::class,
    ]);
});

it('fails if jobs by class name do not match', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()
        ->chain([
            new SimpleJob,
            PlainQueueableExecutable::prepare()->execute(),
            ShouldBeUniqueByInterfaceExecutable::prepare()->execute(),
        ])
        ->execute();

    $assertion = PlainQueueableExecutable::assert()->queued()->withChain([
        PlainQueueableExecutable::class,
        SimpleJob::class,
        ShouldBeUniqueByInterfaceExecutable::class,
    ]);

    expect(fn () => $assertion->__destruct())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was not queued with chain.');
});

it('passes if jobs by callable match', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()
        ->chain([
            new SimpleJob,
            PlainQueueableExecutable::prepare()->execute(),
            ShouldBeUniqueByInterfaceExecutable::prepare()->execute(),
        ])
        ->execute('input');

    PlainQueueableExecutable::assert()->queued()->withChain([
        fn (SimpleJob $job) => true,
        fn (PlainQueueableExecutable $job) => true,
        fn (PushedJob $job) => $job->is(ShouldBeUniqueByInterfaceExecutable::class),
    ]);
});

it('fails if jobs by callable do not match', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()
        ->chain([
            new SimpleJob,
            PlainQueueableExecutable::prepare()->execute(),
            ShouldBeUniqueByInterfaceExecutable::prepare()->execute(),
        ])
        ->execute();

    $assertion = PlainQueueableExecutable::assert()->queued()->withChain([
        fn (PushedJob $job) => $job->is(ShouldBeUniqueByInterfaceExecutable::class),
        fn (SimpleJob $job) => true,
        fn (PlainQueueableExecutable $job) => true,
    ]);

    expect(fn () => $assertion->__destruct())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was not queued with chain.');
});

it('passes when executable was queued with chain from bus', function () {
    Queue::fake();

    Bus::chain([
        PlainQueueableExecutable::prepare()->execute('first'),
        PlainQueueableExecutable::prepare()->execute('second'),
        PlainQueueableExecutable::prepare()->execute('third'),
    ])
        ->dispatch();

    PlainQueueableExecutable::assert()->queued()->withChain([
        PlainQueueableExecutable::class,
        PlainQueueableExecutable::class,
    ]);
});

it('fails when executable was not queued with chain from bus', function () {
    Queue::fake();

    Bus::chain([
        PlainQueueableExecutable::prepare()->execute('first'),
        PlainQueueableExecutable::prepare()->execute('second'),
        PlainQueueableExecutable::prepare()->execute('third'),
    ])
        ->dispatch();

    $assertion = PlainQueueableExecutable::assert()->queued()->withChain([
        PlainQueueableExecutable::class,
    ]);

    expect(fn () => $assertion->__destruct())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was not queued with chain.');
});
