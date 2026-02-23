<?php

declare(strict_types=1);

use PHPUnit\Framework\ExpectationFailedException;
use Workbench\App\Executables\Testing\PrependingToChainExecutable;
use Workbench\App\Jobs\SimpleEncryptedJob;
use Workbench\App\Jobs\SimpleJob;

it('fails when executable is not tested', function () {
    PrependingToChainExecutable::sync()->execute(
        new SimpleJob
    );

    expect(function () {
        PrependingToChainExecutable::assert()->hasChain([
            SimpleJob::class,
        ]);
    })->toThrow(ExpectationFailedException::class, 'Testing not active for [PrependingToChainExecutable]. Use [PrependingToChainExecutable::test()]');
});

it('passes when executable has matching chain', function () {
    PrependingToChainExecutable::test()
        ->execute(new SimpleJob);

    PrependingToChainExecutable::assert()->hasChain([
        SimpleJob::class,
    ]);
});

it('fails when executable does not have matching chain', function () {
    PrependingToChainExecutable::test()
        ->execute(new SimpleJob);

    expect(function () {
        PrependingToChainExecutable::assert()->hasChain([
            SimpleEncryptedJob::class,
            SimpleJob::class,
        ]);
    })->toThrow(ExpectationFailedException::class, '[PrependingToChainExecutable] does not have the expected chain.');
});
