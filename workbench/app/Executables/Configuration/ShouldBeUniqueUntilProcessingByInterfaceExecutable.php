<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Havn\Executable\QueueableExecutable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class ShouldBeUniqueUntilProcessingByInterfaceExecutable implements ShouldBeUniqueUntilProcessing
{
    use QueueableExecutable;

    public int $uniqueFor = 360;

    public function execute(): void
    {
        // ..
    }
}
