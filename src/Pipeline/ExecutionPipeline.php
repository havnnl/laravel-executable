<?php

declare(strict_types=1);

namespace Havn\Executable\Pipeline;

use Havn\Executable\Contracts\ShouldExecuteInTransaction;
use Havn\Executable\Support\ExecutableArguments;
use Illuminate\Pipeline\Pipeline;

/**
 * @internal
 */
final class ExecutionPipeline
{
    public function __construct(
        private object $executable,
        private ExecutableArguments $arguments,
    ) {}

    public function execute(): mixed
    {
        $pipes = $this->buildPipeline();

        if (empty($pipes)) {
            return $this->arguments->callOn($this->executable, 'execute');
        }

        return app(Pipeline::class)
            ->send($this)
            ->through($pipes)
            ->then(fn (): mixed => $this->arguments->callOn($this->executable, 'execute'));
    }

    /**
     * @return list<object>
     */
    private function buildPipeline(): array
    {
        return array_filter([
            $this->transactionPipe(),
        ]);
    }

    private function transactionPipe(): ?ExecuteInTransactionPipe
    {
        if (! $this->executable instanceof ShouldExecuteInTransaction) {
            return null;
        }

        $attempts = property_exists($this->executable, 'transactionAttempts')
            ? $this->executable->transactionAttempts
            : 1;

        return new ExecuteInTransactionPipe($attempts);
    }
}
