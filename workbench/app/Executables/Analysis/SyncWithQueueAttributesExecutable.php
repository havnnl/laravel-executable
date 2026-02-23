<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Havn\Executable\Executable;
use Illuminate\Queue\Attributes\WithoutRelations;
use Workbench\App\Models\SomeModel;

#[WithoutRelations]
class SyncWithQueueAttributesExecutable
{
    use Executable;

    public function execute(SomeModel $user): void {}
}
