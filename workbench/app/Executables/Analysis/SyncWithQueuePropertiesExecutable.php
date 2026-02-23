<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Havn\Executable\Executable;
use Workbench\App\Models\SomeModel;

class SyncWithQueuePropertiesExecutable
{
    use Executable;

    public int $tries = 3;

    public int $timeout = 30;

    public function execute(SomeModel $user): void {}
}
