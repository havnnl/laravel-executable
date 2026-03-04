<?php

declare(strict_types=1);

namespace Workbench\App\Executables\Analysis;

use Havn\Executable\Attributes\ConcurrencyLimit;
use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\QueueableExecutable;
use Throwable;
use Workbench\App\Models\SomeModel;

class ValidExecutable
{
    use QueueableExecutable;

    public function execute(SomeModel $user, string $action): void {}

    public function concurrencyLimit(SomeModel $user): ConcurrencyLimit
    {
        return new ConcurrencyLimit(key: 'test');
    }

    public function retryUntil(SomeModel $user): ?\DateTimeInterface
    {
        return null;
    }

    /**
     * @return array<int, string>
     */
    public function tags(string $action, SomeModel $user): array
    {
        return [];
    }

    public function failed(Throwable $exception, SomeModel $user): void {}

    public function configure(QueueableConfig $config, SomeModel $user): void {}
}
