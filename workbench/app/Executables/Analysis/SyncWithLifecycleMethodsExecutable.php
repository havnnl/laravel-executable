<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Havn\Executable\Executable;
use Workbench\App\Models\SomeModel;

class SyncWithLifecycleMethodsExecutable
{
    use Executable;

    public function execute(SomeModel $user): void {}

    public function retryUntil(SomeModel $user): \DateTimeInterface
    {
        return now()->addHour();
    }

    /**
     * @return array<int, string>
     */
    public function tags(SomeModel $user): array
    {
        return ['user:'.$user->getKey()];
    }
}
