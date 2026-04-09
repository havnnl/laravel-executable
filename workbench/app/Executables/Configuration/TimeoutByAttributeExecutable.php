<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Havn\Executable\QueueableExecutable;
use Illuminate\Queue\Attributes\Timeout;

#[Timeout(120)]
class TimeoutByAttributeExecutable
{
    use QueueableExecutable;

    public function execute(): void
    {
        // ..
    }
}
