<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Havn\Executable\Executable;
use Workbench\App\Models\SomeModel;

class CleanSyncExecutable
{
    use Executable;

    public function execute(SomeModel $user): void {}
}
