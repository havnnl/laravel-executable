<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Workbench\App\Models\SomeModel;

class DirectExecuteCallFixture
{
    public function directCall(ValidExecutable $executable): void
    {
        $executable->execute(new SomeModel, 'action');
    }
}
