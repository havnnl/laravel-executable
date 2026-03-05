<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\Config\ConcurrencyLimit;
use Havn\Executable\QueueableExecutable;

class UseConcurrencyLimitExecutable
{
    use QueueableExecutable;

    public function concurrencyLimit(): ConcurrencyLimit
    {
        return new ConcurrencyLimit(
            key: 'test-concurrency',
        );
    }

    public function execute(mixed $return): mixed
    {
        return $return;
    }
}
