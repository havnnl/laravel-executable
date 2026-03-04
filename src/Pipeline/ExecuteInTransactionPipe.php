<?php

declare(strict_types=1);

namespace Havn\Executable\Pipeline;

use Closure;
use Havn\Executable\Attributes\ExecuteInTransaction;
use Illuminate\Support\Facades\DB;

/**
 * @internal
 */
final class ExecuteInTransactionPipe
{
    public function __construct(private ExecuteInTransaction $config) {}

    public function handle(mixed $passable, Closure $next): mixed
    {
        return DB::transaction(fn (): mixed => $next($passable), $this->config->attempts);
    }
}
