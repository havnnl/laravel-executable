<?php

declare(strict_types=1);

use Havn\Executable\Testing\Queueing\PushedJob;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\PlainQueueableExecutable;

it('fails when queue is not faked', function () {
    expect(fn () => PlainQueueableExecutable::assert()->queued())
        ->toThrow(ExpectationFailedException::class, 'Queue was not faked. Use [Queue::fake()]');
});

it('passes when executable was queued', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()->execute('input');

    PlainQueueableExecutable::assert()->queued();
});

it('fails when executable was not queued', function () {
    Queue::fake();

    expect(fn () => PlainQueueableExecutable::assert()->queued())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was not queued.');
});

it('passes asserting with where clause when executable was queued', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()->execute('input');

    PlainQueueableExecutable::assert()->queued()->where(
        fn (PushedJob $pushed) => $pushed->executedWith('input')
    );
});

it('fails asserting with where clause when executable was not queued', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()->execute('input');

    $assertion = PlainQueueableExecutable::assert()->queued()->where(fn (PushedJob $pushed) => $pushed->executedWith('other input'));

    expect(fn () => $assertion->__destruct())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was not queued matching custom filters.');
});

it('passes executable was queued x times', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()->execute('input');
    PlainQueueableExecutable::onQueue()->execute('input');

    PlainQueueableExecutable::assert()->queued()->where(
        fn (PushedJob $pushed) => $pushed->executedWith('input')
    )->times(2);
});

it('fails executable was not queued x times', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()->execute('input');

    $assertion = PlainQueueableExecutable::assert()->queued()->where(
        fn (PushedJob $pushed) => $pushed->executedWith('input')
    )->times(2);

    expect(fn () => $assertion->__destruct())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was queued [1] times instead of [2] times matching custom filters.');
});
