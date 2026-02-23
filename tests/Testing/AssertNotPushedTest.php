<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\PlainQueueableExecutable;

it('fails when queue is not faked', function () {
    expect(fn () => PlainQueueableExecutable::assert()->notQueued())
        ->toThrow(ExpectationFailedException::class, 'Queue was not faked. Use [Queue::fake()]');
});

it('passes when executable was not queued', function () {
    Queue::fake();

    PlainQueueableExecutable::assert()->notQueued();
});

it('fails when executable was queued', function () {
    Queue::fake();

    PlainQueueableExecutable::onQueue()->execute('input');
    PlainQueueableExecutable::onQueue()->execute('input');

    expect(fn () => PlainQueueableExecutable::assert()->notQueued())
        ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was queued [2] times instead of [0] times.');

});
