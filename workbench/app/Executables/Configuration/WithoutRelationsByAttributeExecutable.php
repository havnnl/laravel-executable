<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Havn\Executable\QueueableExecutable;
use Illuminate\Queue\Attributes\WithoutRelations;
use Workbench\App\Models\SomeModel;

#[WithoutRelations]
class WithoutRelationsByAttributeExecutable
{
    use QueueableExecutable;

    public function execute(SomeModel $model): void
    {
        // ..
    }
}
