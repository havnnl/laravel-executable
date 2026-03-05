<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\QueueableExecutable;

class UseConcurrencyLimitWithInvalidReturnExecutable
{
    use QueueableExecutable;

    public function concurrencyLimit(): string
    {
        return 'not-a-concurrency-limit';
    }

    public function execute(mixed $return): mixed
    {
        return $return;
    }
}
