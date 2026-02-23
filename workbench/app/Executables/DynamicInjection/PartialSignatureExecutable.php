<?php

declare(strict_types=1);

namespace Workbench\App\Executables\DynamicInjection;

use Havn\Executable\QueueableExecutable;

class PartialSignatureExecutable
{
    use QueueableExecutable;

    public function execute(string $orderId, int $amount, string $currency): void
    {
        // ..
    }

    /**
     * Config method that declares only a subset of execute() params.
     * Should receive the correct $orderId value via named resolution.
     */
    public function displayName(string $orderId): string
    {
        $_SERVER['_partial_signature_display_name_order_id'] = $orderId;

        return 'order-'.$orderId;
    }

    /**
     * Tags method that declares only $amount from execute() params.
     *
     * @return array<int, string>
     */
    public function tags(int $amount): array
    {
        $_SERVER['_partial_signature_tags_amount'] = $amount;

        return ['amount:'.$amount];
    }

    /**
     * Middleware method that declares only $orderId from execute() params.
     *
     * @return array<int, string>
     */
    public function middleware(string $orderId): array
    {
        $_SERVER['_partial_signature_middleware_order_id'] = $orderId;

        return ['order-middleware:'.$orderId];
    }

    /**
     * UniqueId method that declares only $orderId from execute() params.
     */
    public function uniqueId(string $orderId): string
    {
        $_SERVER['_partial_signature_unique_id_order_id'] = $orderId;

        return $orderId;
    }

    /**
     * Tries method that declares only $amount from execute() params.
     */
    public function tries(int $amount): int
    {
        $_SERVER['_partial_signature_tries_amount'] = $amount;

        return $amount > 1000 ? 5 : 3;
    }
}
