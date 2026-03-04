<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\Attributes\ConcurrencyLimit;
use Havn\Executable\QueueableExecutable;

class UseConcurrencyLimitWithOptionsExecutable
{
    use QueueableExecutable;

    public function concurrencyLimit(): ConcurrencyLimit
    {
        return new ConcurrencyLimit(
            key: 'test-concurrency',
            lockFor: 120,
            waitFor: 30,
            store: 'redis',
        );
    }

    public function execute(mixed $return): mixed
    {
        return $return;
    }
}
