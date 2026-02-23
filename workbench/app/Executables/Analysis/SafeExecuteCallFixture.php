<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Workbench\App\Models\SomeModel;

class SafeExecuteCallFixture
{
    public function syncStaticCall(): void
    {
        ValidExecutable::sync()->execute(new SomeModel, 'action');
    }

    public function queueStaticCall(): void
    {
        ValidExecutable::onQueue()->execute(new SomeModel, 'action');
    }
}
