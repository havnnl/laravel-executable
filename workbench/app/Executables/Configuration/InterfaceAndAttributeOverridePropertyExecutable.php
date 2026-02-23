<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Configuration;

use Havn\Executable\QueueableExecutable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\WithoutRelations;

#[DeleteWhenMissingModels]
#[WithoutRelations]
class InterfaceAndAttributeOverridePropertyExecutable implements ShouldBeEncrypted, ShouldQueueAfterCommit
{
    use QueueableExecutable;

    public bool $afterCommit = false;

    public bool $deleteWhenMissingModels = false;

    public bool $shouldBeEncrypted = false;

    public bool $withoutRelations = false;

    public function execute(): void
    {
        // ..
    }
}
