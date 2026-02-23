<?php

declare(strict_types=1);

namespace Workbench\App\Executables;

use Havn\Executable\Contracts\ShouldExecuteInTransaction;
use Havn\Executable\QueueableExecutable;

class UseTransactionWithAttemptsExecutable implements ShouldExecuteInTransaction
{
    use QueueableExecutable;

    public int $transactionAttempts = 3;

    public function execute(mixed $return): mixed
    {
        return $return;
    }
}
