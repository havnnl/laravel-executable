<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\PlainQueueableExecutable;

it('fails when queue is not faked', function () {
    expect(fn () => PlainQueueableExecutable::assert()->queued()->times(1))
        ->toThrow(ExpectationFailedException::class, 'Queue was not faked. Use [Queue::fake()]');
});

it('passes when executable was queued x times', function () {
    Queue::fake();

    PlainQueueableExecutable::assert()->queued()->times(0);

    PlainQueueableExecutable::onQueue()->execute('input');
    PlainQueueableExecutable::onQueue()->execute('input');

    PlainQueueableExecutable::assert()->queued()->times(2);
});

it('fails when executable was not queued x times', function () {
    Queue::fake();

    $assertion = PlainQueueableExecutable::assert()->queued()->times(2);

    expect(fn () => $assertion->__destruct())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was queued [0] times instead of [2] times.');
});
