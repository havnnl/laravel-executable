<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Analysis;

use Havn\Executable\Executable;
use Havn\Executable\QueueableExecutable;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * Overrides the return type of execute() on contract interfaces so that
 * PHPStan resolves the executable's own return type instead of PendingExecution.
 *
 * @internal
 */
final class ExecuteReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @param  class-string  $class
     * @param  class-string|null  $forceReturnType
     */
    public function __construct(
        private string $class,
        private ?string $forceReturnType = null,
    ) {}

    public function getClass(): string
    {
        return $this->class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'execute';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope,
    ): ?Type {
        if ($this->forceReturnType !== null) {
            return new ObjectType($this->forceReturnType);
        }

        $callerType = $scope->getType($methodCall->var);

        foreach ($callerType->getObjectClassReflections() as $reflection) {
            if (! $reflection->hasTraitUse(Executable::class) && ! $reflection->hasTraitUse(QueueableExecutable::class)) {
                continue;
            }

            if (! $reflection->hasNativeMethod('execute')) {
                continue;
            }

            return $reflection->getNativeMethod('execute')->getVariants()[0]->getReturnType();
        }

        return null;
    }
}
