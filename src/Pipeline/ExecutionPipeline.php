<?php

declare(strict_types=1);

namespace Havn\Executable\Pipeline;

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
        $config = PipelineConfig::resolve($this->executable, $this->arguments);

        return array_filter([
            $config->concurrencyLimit ? new LimitConcurrencyPipe($config->concurrencyLimit) : null,
            $config->executeInTransaction ? new ExecuteInTransactionPipe($config->executeInTransaction) : null,
        ]);
    }
}
