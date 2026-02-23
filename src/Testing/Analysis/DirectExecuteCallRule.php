<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Analysis;

use Havn\Executable\Executable;
use Havn\Executable\QueueableExecutable;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @internal
 *
 * @implements Rule<MethodCall>
 */
final class DirectExecuteCallRule implements Rule
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @return list<RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Identifier || $node->name->name !== 'execute') {
            return [];
        }

        $callerType = $scope->getType($node->var);
        $reflections = $callerType->getObjectClassReflections();

        if ($reflections === []) {
            return [];
        }

        foreach ($reflections as $reflection) {
            if (! $reflection->hasTraitUse(Executable::class) && ! $reflection->hasTraitUse(QueueableExecutable::class)) {
                return [];
            }
        }

        $caller = $node->var instanceof Variable && is_string($node->var->name)
            ? sprintf('$%s->sync()', $node->var->name)
            : sprintf('%s::sync()', $reflections[0]->getNativeReflection()->getShortName());

        return [
            RuleErrorBuilder::message(
                sprintf('Calling execute() directly bypasses the execution pipeline. Use %s->execute() instead.', $caller)
            )
                ->identifier('executable.directExecuteCall')
                ->build(),
        ];
    }
}
