<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Havn\Executable\QueueableExecutable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

class ShouldDispatchAfterCommitByInterfaceExecutable implements ShouldQueueAfterCommit
{
    use QueueableExecutable;

    public function execute(): void
    {
        // ..
    }
}
