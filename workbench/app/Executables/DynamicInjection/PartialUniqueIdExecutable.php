<?php

declare(strict_types=1);

namespace Workbench\App\Executables\DynamicInjection;

use Havn\Executable\QueueableExecutable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class PartialUniqueIdExecutable implements ShouldBeUnique
{
    use QueueableExecutable;

    public function execute(string $orderId, int $amount): void
    {
        // ..
    }

    /**
     * UniqueId method that declares only $orderId from execute() params.
     */
    public function uniqueId(string $orderId): string
    {
        $_SERVER['_partial_unique_id_order_id'] = $orderId;

        return $orderId;
    }
}
