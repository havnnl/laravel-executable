<?php

declare(strict_types=1);

namespace Havn\Executable\Jobs;

use Havn\Executable\Pipeline\ExecutionPipeline;
use Havn\Executable\Support\ExecutableArguments;

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
        return (new ExecutionPipeline($this->executable, $this->arguments))->execute();
    }
}
