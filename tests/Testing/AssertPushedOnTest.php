<?php

declare(strict_types=1);

use Havn\Executable\Testing\Queueing\PushedJob;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\PlainQueueableExecutable;

it('fails when queue is not faked', function () {
    expect(fn () => PlainQueueableExecutable::assert()->queued()->onQueue('some-queue'))
        ->toThrow(ExpectationFailedException::class, 'Queue was not faked. Use [Queue::fake()]');
});

it('passes when executable was queued on queue', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue('some-queue')->execute('input');

    PlainQueueableExecutable::assert()->queued()->onQueue('some-queue');
});

it('fails when executable was queued on different queue', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue('some-queue')->execute('input');

    $assertion = PlainQueueableExecutable::assert()->queued()->onQueue('other-queue');

    expect(fn () => $assertion->__destruct())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was not queued on queue [other-queue].');
});

it('passes asserting with where clause when executable was queued on queue', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue('some-queue')->execute('input');

    PlainQueueableExecutable::assert()->queued()->onQueue('some-queue')->where(
        fn (PushedJob $pushed) => $pushed->executedWith('input')
    );
});

it('fails asserting with where clause when executable was not queued on queue', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue('some-queue')->execute('input');

    $assertion = PlainQueueableExecutable::assert()->queued()->onQueue('other-queue')->where(
        fn (PushedJob $pushed) => $pushed->executedWith('input')
    );

    expect(fn () => $assertion->__destruct())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was not queued on queue [other-queue] matching custom filters.');
});

it('passes executable was queued x times', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue('some-queue')->execute('input');
    PlainQueueableExecutable::onQueue('some-queue')->execute('input');

    PlainQueueableExecutable::assert()->queued()->onQueue('some-queue')->where(
        fn (PushedJob $pushed) => $pushed->executedWith('input')
    )->times(2);
});

it('fails executable was not queued x times', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue('some-queue')->execute('input');

    $assertion = PlainQueueableExecutable::assert()->queued()->onQueue('some-queue')->where(
        fn (PushedJob $pushed) => $pushed->executedWith('input')
    )->times(2);

    expect(fn () => $assertion->__destruct())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was queued [1] times instead of [2] times on queue [some-queue] matching custom filters.');
});
