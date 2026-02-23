<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Analysis;

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\QueueableExecutable;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedParameterReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;
use Throwable;

/**
 * @internal
 *
 * @implements Rule<InClassNode>
 */
final class ExecutableParamMismatchRule implements Rule
{
    private const array LIFECYCLE_METHODS = [
        'configure',
        'backoff',
        'displayName',
        'retryUntil',
        'tries',
        'uniqueFor',
        'uniqueId',
        'tags',
        'middleware',
        'failed',
        'uniqueVia',
    ];

    private const array FIRST_PARAMETER_TYPES = [
        'failed' => Throwable::class,
        'configure' => QueueableConfig::class,
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

        if (! $classReflection->hasTraitUse(QueueableExecutable::class)) {
            return [];
        }

        return array_map(
            fn (ParameterMismatch $mismatch) => RuleErrorBuilder::message($mismatch->message)
                ->identifier('executable.paramMismatch')
                ->build(),
            $this->analyze($classReflection),
        );
    }

    /**
     * @return list<ParameterMismatch>
     */
    private function analyze(ClassReflection $classReflection): array
    {
        if (! $classReflection->hasNativeMethod('execute')) {
            return [];
        }

        $executeParams = $classReflection->getNativeMethod('execute')
            ->getVariants()[0]->getParameters();

        $mismatches = [];

        foreach (self::LIFECYCLE_METHODS as $methodName) {
            if (! $classReflection->hasNativeMethod($methodName)) {
                continue;
            }

            $params = $classReflection->getNativeMethod($methodName)
                ->getVariants()[0]->getParameters();

            $firstParamMismatch = $this->validateFirstParameter($classReflection, $methodName, $params);

            if ($firstParamMismatch !== null) {
                $mismatches[] = $firstParamMismatch;

                continue;
            }

            $params = $this->filterFrameworkParameters($methodName, $params);

            foreach ($params as $param) {
                $mismatch = $this->checkParameter($classReflection, $methodName, $param, $executeParams);

                if ($mismatch !== null) {
                    $mismatches[] = $mismatch;
                }
            }
        }

        return $mismatches;
    }

    /**
     * @param  list<ExtendedParameterReflection>  $params
     */
    private function validateFirstParameter(ClassReflection $classReflection, string $methodName, array $params): ?ParameterMismatch
    {
        $expectedClass = self::FIRST_PARAMETER_TYPES[$methodName] ?? null;

        if ($expectedClass === null || $params === []) {
            return null;
        }

        $firstParam = $params[0];
        $nativeType = $firstParam->getNativeType();
        $expectedType = new ObjectType($expectedClass);

        if ($expectedType->equals($nativeType)) {
            return null;
        }

        return new ParameterMismatch(
            className: $classReflection->getName(),
            methodName: $methodName,
            parameterName: $firstParam->getName(),
            message: sprintf(
                'Method %s() must declare %s as its first parameter, found %s $%s',
                $methodName,
                ($pos = strrpos($expectedClass, '\\')) !== false ? substr($expectedClass, $pos + 1) : $expectedClass,
                $firstParam->hasNativeType() ? $nativeType->describe(VerbosityLevel::typeOnly()) : 'mixed',
                $firstParam->getName(),
            ),
        );
    }

    /**
     * @param  list<ExtendedParameterReflection>  $params
     * @return list<ExtendedParameterReflection>
     */
    private function filterFrameworkParameters(string $methodName, array $params): array
    {
        $expectedClass = self::FIRST_PARAMETER_TYPES[$methodName] ?? null;

        if ($expectedClass === null || $params === []) {
            return $params;
        }

        $firstParam = $params[0];

        if ($firstParam->hasNativeType() && (new ObjectType($expectedClass))->equals($firstParam->getNativeType())) {
            return array_slice($params, 1);
        }

        return $params;
    }

    /**
     * @param  list<ExtendedParameterReflection>  $executeParams
     */
    private function checkParameter(
        ClassReflection $classReflection,
        string $methodName,
        ExtendedParameterReflection $param,
        array $executeParams,
    ): ?ParameterMismatch {
        if (! $param->hasNativeType()) {
            return null;
        }

        $paramName = $param->getName();
        $paramType = $param->getNativeType();

        $nameMatch = null;
        $typeMatch = null;

        foreach ($executeParams as $executeParam) {
            if ($paramName === $executeParam->getName()) {
                if ($paramType->equals($executeParam->getNativeType())) {
                    return null;
                }

                $nameMatch = $executeParam;
            }

            if ($paramType->equals($executeParam->getNativeType())) {
                $typeMatch = $executeParam;
            }
        }

        if ($typeMatch !== null) {
            return new ParameterMismatch(
                className: $classReflection->getName(),
                methodName: $methodName,
                parameterName: $paramName,
                message: sprintf(
                    'Parameter $%s on method %s() has type %s matching execute() parameter $%s — did you mean $%s?',
                    $paramName,
                    $methodName,
                    $paramType->describe(VerbosityLevel::typeOnly()),
                    $typeMatch->getName(),
                    $typeMatch->getName(),
                ),
            );
        }

        if ($nameMatch !== null) {
            return new ParameterMismatch(
                className: $classReflection->getName(),
                methodName: $methodName,
                parameterName: $paramName,
                message: sprintf(
                    'Parameter $%s on method %s() has type %s but execute() declares $%s as %s',
                    $paramName,
                    $methodName,
                    $paramType->describe(VerbosityLevel::typeOnly()),
                    $nameMatch->getName(),
                    $nameMatch->getNativeType()->describe(VerbosityLevel::typeOnly()),
                ),
            );
        }

        return new ParameterMismatch(
            className: $classReflection->getName(),
            methodName: $methodName,
            parameterName: $paramName,
            message: sprintf(
                'Parameter $%s on method %s() is not declared on execute()',
                $paramName,
                $methodName,
            ),
        );
    }
}
