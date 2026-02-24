<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\PlainQueueableExecutable;

it('fails when queue is not faked', function () {
    expect(fn () => PlainQueueableExecutable::assert()->queued()->with(123))
        ->toThrow(ExpectationFailedException::class, 'Queue was not faked. Use [Queue::fake()]');
});

it('passes when executable was queued with given input', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()->execute('input');

    PlainQueueableExecutable::assert()->queued()->with('input');
});

it('fails when executable was not queued with given input', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()->execute('input');

    $assertion = PlainQueueableExecutable::assert()->queued()->with('other-input');

    expect(fn () => $assertion->__destruct())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was not queued with specific arguments.');
});

it('fails when count mismatches with matching argument filter', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()->execute('input');

    $assertion = fn () => PlainQueueableExecutable::assert()
        ->queued()
        ->times(2)
        ->with('input');

    expect($assertion)
        ->toThrow(ExpectationFailedException::class, 'was queued [1] times instead of [2] times');
});

it('does not create a reference cycle preventing destructor assertions', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()->execute('input');

    $assertion = PlainQueueableExecutable::assert()
        ->queued()
        ->once()
        ->with('input');

    $weak = WeakReference::create($assertion);
    unset($assertion);

    expect($weak->get())->toBeNull();
});
