<?php

declare(strict_types=1);

namespace Havn\Executable\Pipeline;

use Havn\Executable\Attributes\ConcurrencyLimit;
use Havn\Executable\Attributes\ExecuteInTransaction;
use Havn\Executable\Contracts\ShouldExecuteInTransaction;
use Havn\Executable\Support\AttributeReader;
use Havn\Executable\Support\ExecutableArguments;

/**
 * @internal
 */
final class PipelineConfig
{
    public function __construct(
        public readonly ?ConcurrencyLimit $concurrencyLimit = null,
        public readonly ?ExecuteInTransaction $executeInTransaction = null,
    ) {}

    public static function resolve(object $executable, ExecutableArguments $arguments): self
    {
        return new self(
            concurrencyLimit: self::resolveConcurrencyLimit($executable, $arguments),
            executeInTransaction: self::resolveExecuteInTransaction($executable),
        );
    }

    private static function resolveConcurrencyLimit(object $executable, ExecutableArguments $arguments): ?ConcurrencyLimit
    {
        if (method_exists($executable, 'concurrencyLimit')) {
            return $arguments->callOn($executable, 'concurrencyLimit');
        }

        return AttributeReader::firstFromClassHierarchy($executable, ConcurrencyLimit::class);
    }

    private static function resolveExecuteInTransaction(object $executable): ?ExecuteInTransaction
    {
        $attribute = AttributeReader::firstFromClassHierarchy($executable, ExecuteInTransaction::class);

        if ($attribute) {
            return $attribute;
        }

        if ($executable instanceof ShouldExecuteInTransaction) {
            $attempts = property_exists($executable, 'transactionAttempts')
                ? $executable->transactionAttempts
                : 1;

            return new ExecuteInTransaction($attempts);
        }

        return null;
    }
}
