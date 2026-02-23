<?php

declare(strict_types=1);

namespace Havn\Executable\Jobs;

use Havn\Executable\Contracts\ShouldExecuteInTransaction;
use Illuminate\Support\Facades\DB;

/**
 * @internal
 */
final class ExecutableSyncJob
{
    protected string $executableClass;

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function __construct(protected object $executable, protected array $arguments)
    {
        $this->executableClass = get_class($executable);
    }

    public function executableClass(): string
    {
        return $this->executableClass;
    }

    public function executable(): object
    {
        return $this->executable;
    }

    /**
     * @return array<int, mixed>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    public function handle(): mixed
    {
        if (! $this->executable instanceof ShouldExecuteInTransaction) {
            return $this->executable->execute(...$this->arguments);
        }

        $attempts = property_exists($this->executable, 'transactionAttempts')
            ? $this->executable->transactionAttempts
            : 1;

        return DB::transaction(function (): mixed {
            return $this->executable->execute(...$this->arguments);
        }, $attempts);
    }
}
