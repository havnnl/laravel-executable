<?php

declare(strict_types=1);

use Havn\Executable\Testing\ExecutionAssertions;
use Havn\Executable\Testing\Queueing\PushedBatch;
use Havn\Executable\Testing\Queueing\PushedJob;
use Havn\Executable\Testing\Queueing\QueuedAssertion;

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->toOnlyBeUsedIn([
        ExecutionAssertions::class,
        PushedBatch::class,
        PushedJob::class,
        QueuedAssertion::class,
    ]);
