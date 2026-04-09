<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Havn\Executable\QueueableExecutable;
use Illuminate\Queue\Attributes\Timeout;

#[Timeout(99)]
class TimeoutAttributeAndPropertyExecutable
{
    use QueueableExecutable;

    public int $timeout = 10;

    public function execute(): void
    {
        // ..
    }
}
