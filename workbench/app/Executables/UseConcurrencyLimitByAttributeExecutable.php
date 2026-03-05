<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\Config\ConcurrencyLimit;
use Havn\Executable\QueueableExecutable;

#[ConcurrencyLimit('test-concurrency')]
class UseConcurrencyLimitByAttributeExecutable
{
    use QueueableExecutable;

    public function execute(mixed $return): mixed
    {
        return $return;
    }
}
