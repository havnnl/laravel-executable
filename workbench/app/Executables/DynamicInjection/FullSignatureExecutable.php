<?php

declare(strict_types=1);

namespace Workbench\App\Executables\DynamicInjection;

use Havn\Executable\QueueableExecutable;
use Throwable;

class FullSignatureExecutable
{
    use QueueableExecutable;

    public function execute(string $orderId, int $amount): mixed
    {
        return $orderId;
    }

    /**
     * Tags method with full execute signature (old pattern).
     *
     * @return array<int, string>
     */
    public function tags(string $orderId, int $amount): array
    {
        return ['order:'.$orderId, 'amount:'.$amount];
    }

    /**
     * Failed method with full execute signature (old pattern).
     */
    public function failed(Throwable $exception, string $orderId, int $amount): void
    {
        $_SERVER['_full_sig_failed_exception'] = $exception;
        $_SERVER['_full_sig_failed_order_id'] = $orderId;
        $_SERVER['_full_sig_failed_amount'] = $amount;
    }
}
