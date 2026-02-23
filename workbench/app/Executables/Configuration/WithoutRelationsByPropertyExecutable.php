<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Havn\Executable\QueueableExecutable;
use Workbench\App\Models\SomeModel;

class WithoutRelationsByPropertyExecutable
{
    use QueueableExecutable;

    public bool $withoutRelations = true;

    public function execute(SomeModel $model): void
    {
        // ..
    }
}
