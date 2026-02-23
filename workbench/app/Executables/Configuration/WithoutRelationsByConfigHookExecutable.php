<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\QueueableExecutable;
use Workbench\App\Models\SomeModel;

class WithoutRelationsByConfigHookExecutable
{
    use QueueableExecutable;

    public function configure(QueueableConfig $config): void
    {
        $config->withoutRelations();
    }

    public function execute(SomeModel $model): void
    {
        // ..
    }
}
