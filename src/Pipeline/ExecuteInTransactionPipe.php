<?php

declare(strict_types=1);

namespace Havn\Executable\Pipeline;

use Closure;
use Illuminate\Support\Facades\DB;

/**
 * @internal
 */
final class ExecuteInTransactionPipe
{
    public function __construct(private int $attempts = 1) {}

    public function handle(mixed $passable, Closure $next): mixed
    {
        return DB::transaction(fn (): mixed => $next($passable), $this->attempts);
    }
}
