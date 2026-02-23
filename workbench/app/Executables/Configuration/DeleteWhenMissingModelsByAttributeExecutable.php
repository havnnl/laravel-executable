<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Havn\Executable\QueueableExecutable;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;

#[DeleteWhenMissingModels]
class DeleteWhenMissingModelsByAttributeExecutable
{
    use QueueableExecutable;

    public function execute(): void
    {
        // ..
    }
}
