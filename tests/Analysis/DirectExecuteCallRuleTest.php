<?php

declare(strict_types=1);

namespace Havn\Executable\Tests\Analysis;

use Havn\Executable\Testing\Analysis\DirectExecuteCallRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Workbench\App\Executables\Analysis\DirectExecuteCallFixture;
use Workbench\App\Executables\Analysis\SafeExecuteCallFixture;

/**
 * @extends RuleTestCase<DirectExecuteCallRule>
 */
final class DirectExecuteCallRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DirectExecuteCallRule;
    }

    /** @see DirectExecuteCallFixture */
    public function test_flags_direct_execute_call(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/DirectExecuteCallFixture.php'],
            [
                [
                    'Calling execute() directly bypasses the execution pipeline. Use $executable->sync()->execute() instead.',
                    13,
                ],
            ]
        );
    }

    /** @see SafeExecuteCallFixture */
    public function test_allows_execute_via_sync_and_queue(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/SafeExecuteCallFixture.php'],
            []
        );
    }
}
