<?php

declare(strict_types=1);

namespace Havn\Executable\Jobs;

use Havn\Executable\Contracts\ShouldExecuteInTransaction;
use Havn\Executable\Support\ExecutableArguments;
use Illuminate\Support\Facades\DB;

/**
 * @internal
 */
final class ExecutableSyncJob
{
    protected string $executableClass;

    public function __construct(protected object $executable, protected ExecutableArguments $arguments)
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
     * @return array<int|string, mixed>
     */
    public function arguments(): array
    {
        return $this->arguments->toArray();
    }

    public function handle(): mixed
    {
        if (! $this->executable instanceof ShouldExecuteInTransaction) {
            return $this->arguments->callOn($this->executable, 'execute');
        }

        $attempts = property_exists($this->executable, 'transactionAttempts')
            ? $this->executable->transactionAttempts
            : 1;

        return DB::transaction(function (): mixed {
            return $this->arguments->callOn($this->executable, 'execute');
        }, $attempts);
    }
}
