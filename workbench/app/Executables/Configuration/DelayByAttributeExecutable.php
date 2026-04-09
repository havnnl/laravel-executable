<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Havn\Executable\QueueableExecutable;
use Illuminate\Queue\Attributes\Delay;

#[Delay(60)]
class DelayByAttributeExecutable
{
    use QueueableExecutable;

    public function execute(): void
    {
        // ..
    }
}
