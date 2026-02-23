<?php

declare(strict_types=1);

namespace Workbench\App\Executables\DynamicInjection;

use Exception;
use Havn\Executable\QueueableExecutable;
use Throwable;

class PartialFailedExecutable
{
    use QueueableExecutable;

    public function execute(string $orderId, int $amount, string $currency): mixed
    {
        throw new Exception('Order processing failed');
    }

    /**
     * Failed method that declares Throwable + partial execute params.
     * Should receive the exception and the named orderId value.
     */
    public function failed(Throwable $exception, string $orderId): void
    {
        $_SERVER['_partial_failed_exception'] = $exception;
        $_SERVER['_partial_failed_order_id'] = $orderId;
    }
}
