<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Havn\Executable\QueueableExecutable;
use Illuminate\Queue\Attributes\UniqueFor;

#[UniqueFor(300)]
class UniqueForByAttributeExecutable
{
    use QueueableExecutable;

    public function execute(): void
    {
        // ..
    }
}
