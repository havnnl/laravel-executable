<?php

declare(strict_types=1);

namespace Workbench\App\Executables\DynamicInjection;

use Exception;
use Havn\Executable\QueueableExecutable;

class SubclassFailedExecutable
{
    use QueueableExecutable;

    public function execute(string $orderId, int $amount): mixed
    {
        throw new Exception('Subclass failure');
    }

    /**
     * Failed method with Exception subclass type-hint and custom parameter name.
     */
    public function failed(Exception $error, string $orderId): void
    {
        $_SERVER['_subclass_failed_exception'] = $error;
        $_SERVER['_subclass_failed_order_id'] = $orderId;
    }
}
