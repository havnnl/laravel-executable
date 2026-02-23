<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Havn\Executable\Executable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Queue\Attributes\WithoutRelations;
use Workbench\App\Models\SomeModel;

#[WithoutRelations]
class SyncWithAllQueueFeaturesExecutable implements ShouldBeEncrypted
{
    use Executable;

    public int $tries = 3;

    public function execute(SomeModel $user): void {}

    /**
     * @return array<int, string>
     */
    public function tags(SomeModel $user): array
    {
        return ['user:'.$user->getKey()];
    }
}
