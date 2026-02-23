<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Analysis;

use Havn\Executable\Executable;
use Havn\Executable\QueueableExecutable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\WithoutRelations;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @internal
 *
 * @implements Rule<InClassNode>
 */
final class SyncExecutableWithQueueFeaturesRule implements Rule
{
    /** @var list<string> */
    private const array QUEUE_PROPERTIES = [
        'afterCommit',
        'backoff',
        'chainConnection',
        'chainQueue',
        'connection',
        'delay',
        'deleteWhenMissingModels',
        'failOnTimeout',
        'maxExceptions',
        'middleware',
        'queue',
        'retryUntil',
        'shouldBeEncrypted',
        'timeout',
        'tries',
        'uniqueFor',
        'uniqueId',
        'withoutRelations',
    ];

    /** @var list<string> */
    private const array LIFECYCLE_METHODS = [
        'backoff',
        'configure',
        'displayName',
        'failed',
        'middleware',
        'retryUntil',
        'tags',
        'tries',
        'uniqueFor',
        'uniqueId',
        'uniqueVia',
    ];

    /** @var list<class-string> */
    private const array QUEUE_INTERFACES = [
        ShouldBeEncrypted::class,
        ShouldBeUnique::class,
        ShouldBeUniqueUntilProcessing::class,
        ShouldQueueAfterCommit::class,
    ];

    /** @var list<class-string> */
    private const array QUEUE_ATTRIBUTES = [
        DeleteWhenMissingModels::class,
        WithoutRelations::class,
    ];

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @return list<RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();

        if (! $this->isSyncOnlyExecutable($classReflection)) {
            return [];
        }

        $shortName = $classReflection->getNativeReflection()->getShortName();
        $errors = [];

        foreach ($this->findQueueProperties($classReflection) as $property) {
            $errors[] = RuleErrorBuilder::message(
                sprintf('Class %s uses Executable (sync-only) but declares queue property $%s. This has no effect without the QueueableExecutable trait.', $shortName, $property)
            )
                ->identifier('executable.syncWithQueueFeatures')
                ->build();
        }

        foreach ($this->findLifecycleMethods($classReflection) as $method) {
            $errors[] = RuleErrorBuilder::message(
                sprintf('Class %s uses Executable (sync-only) but declares queue lifecycle method %s(). This has no effect without the QueueableExecutable trait.', $shortName, $method)
            )
                ->identifier('executable.syncWithQueueFeatures')
                ->build();
        }

        foreach ($this->findQueueInterfaces($classReflection) as $interface) {
            $shortInterface = ($pos = strrpos($interface, '\\')) !== false ? substr($interface, $pos + 1) : $interface;

            $errors[] = RuleErrorBuilder::message(
                sprintf('Class %s uses Executable (sync-only) but implements %s. This has no effect without the QueueableExecutable trait.', $shortName, $shortInterface)
            )
                ->identifier('executable.syncWithQueueFeatures')
                ->build();
        }

        foreach ($this->findQueueAttributes($classReflection) as $attribute) {
            $shortAttribute = ($pos = strrpos($attribute, '\\')) !== false ? substr($attribute, $pos + 1) : $attribute;

            $errors[] = RuleErrorBuilder::message(
                sprintf('Class %s uses Executable (sync-only) but has attribute #[%s]. This has no effect without the QueueableExecutable trait.', $shortName, $shortAttribute)
            )
                ->identifier('executable.syncWithQueueFeatures')
                ->build();
        }

        usort($errors, fn (RuleError $a, RuleError $b) => $a->getMessage() <=> $b->getMessage());

        return $errors;
    }

    private function isSyncOnlyExecutable(ClassReflection $classReflection): bool
    {
        return $classReflection->hasTraitUse(Executable::class)
            && ! $classReflection->hasTraitUse(QueueableExecutable::class);
    }

    /**
     * @return list<string>
     */
    private function findQueueProperties(ClassReflection $classReflection): array
    {
        $found = [];

        foreach (self::QUEUE_PROPERTIES as $property) {
            if ($classReflection->hasNativeProperty($property)) {
                $found[] = $property;
            }
        }

        return $found;
    }

    /**
     * @return list<string>
     */
    private function findLifecycleMethods(ClassReflection $classReflection): array
    {
        $found = [];

        foreach (self::LIFECYCLE_METHODS as $method) {
            if ($classReflection->hasNativeMethod($method)) {
                $found[] = $method;
            }
        }

        return $found;
    }

    /**
     * @return list<class-string>
     */
    private function findQueueInterfaces(ClassReflection $classReflection): array
    {
        $found = [];

        foreach (self::QUEUE_INTERFACES as $interface) {
            if ($classReflection->implementsInterface($interface)) {
                $found[] = $interface;
            }
        }

        return $found;
    }

    /**
     * @return list<class-string>
     */
    private function findQueueAttributes(ClassReflection $classReflection): array
    {
        $found = [];
        $nativeReflection = $classReflection->getNativeReflection();

        foreach (self::QUEUE_ATTRIBUTES as $attribute) {
            if (! empty($nativeReflection->getAttributes($attribute))) {
                $found[] = $attribute;
            }
        }

        return $found;
    }
}
