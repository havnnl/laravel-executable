<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\Attributes\ConcurrencyLimit;
use Havn\Executable\QueueableExecutable;

#[ConcurrencyLimit('test-concurrency', lockFor: 120, waitFor: 30, store: 'redis')]
class UseConcurrencyLimitByAttributeWithOptionsExecutable
{
    use QueueableExecutable;

    public function execute(mixed $return): mixed
    {
        return $return;
    }
}
