<?php

declare(strict_types=1);

namespace Havn\Executable\Pipeline;

use Havn\Executable\Config\ConcurrencyLimit;
use Havn\Executable\Contracts\ShouldExecuteInTransaction;
use Havn\Executable\Support\AttributeReader;
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
            $this->concurrencyLimitPipe(),
            $this->transactionPipe(),
        ]);
    }

    private function concurrencyLimitPipe(): ?LimitConcurrencyPipe
    {
        if (method_exists($this->executable, 'concurrencyLimit')) {
            return new LimitConcurrencyPipe(
                $this->arguments->callOn($this->executable, 'concurrencyLimit'),
            );
        }

        $attribute = AttributeReader::firstFromClassHierarchy($this->executable, ConcurrencyLimit::class);

        if ($attribute) {
            return new LimitConcurrencyPipe($attribute);
        }

        return null;
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
