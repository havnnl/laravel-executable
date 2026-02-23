<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Havn\Executable\Executable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Workbench\App\Models\SomeModel;

class SyncWithQueueInterfacesExecutable implements ShouldBeEncrypted
{
    use Executable;

    public function execute(SomeModel $user): void {}
}
