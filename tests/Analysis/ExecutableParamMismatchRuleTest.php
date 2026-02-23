<?php

declare(strict_types=1);

namespace Havn\Executable\Tests\Analysis;

use Havn\Executable\Testing\Analysis\ExecutableParamMismatchRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Workbench\App\Executables\Analysis\ConfigureWithoutQueueableConfigExecutable;
use Workbench\App\Executables\Analysis\ExtraParameterExecutable;
use Workbench\App\Executables\Analysis\FailedWithoutThrowableExecutable;
use Workbench\App\Executables\Analysis\FailedWithSubclassThrowableExecutable;
use Workbench\App\Executables\Analysis\MismatchedNameExecutable;
use Workbench\App\Executables\Analysis\MismatchedTypeExecutable;
use Workbench\App\Executables\Analysis\NoExecuteMethodExecutable;
use Workbench\App\Executables\Analysis\NoLifecycleMethodsExecutable;
use Workbench\App\Executables\Analysis\PartialSignatureExecutable;
use Workbench\App\Executables\Analysis\ValidExecutable;

/**
 * @extends RuleTestCase<ExecutableParamMismatchRule>
 */
final class ExecutableParamMismatchRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ExecutableParamMismatchRule;
    }

    /** @see ValidExecutable */
    public function test_valid_executable_has_no_errors(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/ValidExecutable.php'],
            []
        );
    }

    /** @see MismatchedNameExecutable */
    public function test_detects_mismatched_name(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/MismatchedNameExecutable.php'],
            [
                [
                    'Parameter $customer on method retryUntil() has type Workbench\App\Models\SomeModel matching execute() parameter $user — did you mean $user?',
                    10,
                ],
            ]
        );
    }

    /** @see MismatchedTypeExecutable */
    public function test_detects_mismatched_type(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/MismatchedTypeExecutable.php'],
            [
                [
                    'Parameter $user on method retryUntil() has type string but execute() declares $user as Workbench\App\Models\SomeModel',
                    10,
                ],
            ]
        );
    }

    /** @see ExtraParameterExecutable */
    public function test_detects_extra_parameter(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/ExtraParameterExecutable.php'],
            [
                [
                    'Parameter $label on method tags() is not declared on execute()',
                    10,
                ],
            ]
        );
    }

    /** @see PartialSignatureExecutable */
    public function test_allows_partial_signatures(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/PartialSignatureExecutable.php'],
            []
        );
    }

    /** @see FailedWithSubclassThrowableExecutable */
    public function test_flags_subclass_throwable_on_failed(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/FailedWithSubclassThrowableExecutable.php'],
            [
                [
                    'Method failed() must declare Throwable as its first parameter, found Exception $e',
                    11,
                ],
            ]
        );
    }

    /** @see FailedWithoutThrowableExecutable */
    public function test_flags_failed_without_throwable_first_param(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/FailedWithoutThrowableExecutable.php'],
            [
                [
                    'Method failed() must declare Throwable as its first parameter, found Workbench\App\Models\SomeModel $user',
                    10,
                ],
            ]
        );
    }

    /** @see NoExecuteMethodExecutable */
    public function test_skips_class_without_execute_method(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/NoExecuteMethodExecutable.php'],
            []
        );
    }

    /** @see NoLifecycleMethodsExecutable */
    public function test_skips_class_with_no_lifecycle_methods(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/NoLifecycleMethodsExecutable.php'],
            []
        );
    }

    /** @see ConfigureWithoutQueueableConfigExecutable */
    public function test_flags_configure_without_queueable_config_first_param(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/ConfigureWithoutQueueableConfigExecutable.php'],
            [
                [
                    'Method configure() must declare QueueableConfig as its first parameter, found Workbench\App\Models\SomeModel $user',
                    10,
                ],
            ]
        );
    }
}
