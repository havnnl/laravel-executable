<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\PlainQueueableExecutable;
use Workbench\App\Executables\Testing\PrependingToChainExecutable;
use Workbench\App\Jobs\SimpleJob;

it('fails when executable is not tested', function () {
    PlainQueueableExecutable::sync()->execute();

    expect(fn () => PlainQueueableExecutable::assert()->hasNoChain())
        ->toThrow(ExpectationFailedException::class, 'Testing not active for [PlainQueueableExecutable]. Use [PlainQueueableExecutable::test()]');
});

it('passes when executable does not have chain', function () {
    PlainQueueableExecutable::test()->execute();

    PlainQueueableExecutable::assert()->hasNoChain();
});

it('fails when executable has chain', function () {
    PrependingToChainExecutable::test()->execute(new SimpleJob);

    expect(fn () => PrependingToChainExecutable::assert()->hasNoChain())
        ->toThrow(ExpectationFailedException::class, '[PrependingToChainExecutable] does have a chain unexpectedly.');
});
