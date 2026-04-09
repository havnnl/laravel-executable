<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Havn\Executable\QueueableExecutable;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\WithoutRelations;

#[DeleteWhenMissingModels]
#[WithoutRelations]
#[Tries(3)]
class ParentWithAttributesExecutable
{
    use QueueableExecutable;

    public function execute(): void
    {
        // ..
    }
}
