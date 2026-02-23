<?php

declare(strict_types=1);

namespace Havn\Executable\Tests\Analysis;

use Havn\Executable\Testing\Analysis\SyncExecutableWithQueueFeaturesRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Workbench\App\Executables\Analysis\CleanSyncExecutable;
use Workbench\App\Executables\Analysis\SyncWithAllQueueFeaturesExecutable;
use Workbench\App\Executables\Analysis\SyncWithLifecycleMethodsExecutable;
use Workbench\App\Executables\Analysis\SyncWithQueueAttributesExecutable;
use Workbench\App\Executables\Analysis\SyncWithQueueInterfacesExecutable;
use Workbench\App\Executables\Analysis\SyncWithQueuePropertiesExecutable;
use Workbench\App\Executables\Analysis\ValidExecutable;

/**
 * @extends RuleTestCase<SyncExecutableWithQueueFeaturesRule>
 */
final class SyncExecutableWithQueueFeaturesRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new SyncExecutableWithQueueFeaturesRule;
    }

    /** @see CleanSyncExecutable */
    public function test_no_errors_on_clean_sync_executable(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/CleanSyncExecutable.php'],
            []
        );
    }

    /** @see ValidExecutable */
    public function test_no_errors_on_queueable_executable_with_queue_features(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/ValidExecutable.php'],
            []
        );
    }

    /** @see SyncWithQueuePropertiesExecutable */
    public function test_flags_queue_properties_on_sync_executable(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/SyncWithQueuePropertiesExecutable.php'],
            [
                [
                    'Class SyncWithQueuePropertiesExecutable uses Executable (sync-only) but declares queue property $timeout. This has no effect without the QueueableExecutable trait.',
                    10,
                ],
                [
                    'Class SyncWithQueuePropertiesExecutable uses Executable (sync-only) but declares queue property $tries. This has no effect without the QueueableExecutable trait.',
                    10,
                ],
            ]
        );
    }

    /** @see SyncWithLifecycleMethodsExecutable */
    public function test_flags_lifecycle_methods_on_sync_executable(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/SyncWithLifecycleMethodsExecutable.php'],
            [
                [
                    'Class SyncWithLifecycleMethodsExecutable uses Executable (sync-only) but declares queue lifecycle method retryUntil(). This has no effect without the QueueableExecutable trait.',
                    10,
                ],
                [
                    'Class SyncWithLifecycleMethodsExecutable uses Executable (sync-only) but declares queue lifecycle method tags(). This has no effect without the QueueableExecutable trait.',
                    10,
                ],
            ]
        );
    }

    /** @see SyncWithQueueInterfacesExecutable */
    public function test_flags_queue_interfaces_on_sync_executable(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/SyncWithQueueInterfacesExecutable.php'],
            [
                [
                    'Class SyncWithQueueInterfacesExecutable uses Executable (sync-only) but implements ShouldBeEncrypted. This has no effect without the QueueableExecutable trait.',
                    11,
                ],
            ]
        );
    }

    /** @see SyncWithQueueAttributesExecutable */
    public function test_flags_queue_attributes_on_sync_executable(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/SyncWithQueueAttributesExecutable.php'],
            [
                [
                    'Class SyncWithQueueAttributesExecutable uses Executable (sync-only) but has attribute #[WithoutRelations]. This has no effect without the QueueableExecutable trait.',
                    11,
                ],
            ]
        );
    }

    /** @see SyncWithAllQueueFeaturesExecutable */
    public function test_flags_all_four_categories(): void
    {
        $this->analyse(
            [__DIR__.'./../../workbench/app/Executables/Analysis/SyncWithAllQueueFeaturesExecutable.php'],
            [
                [
                    'Class SyncWithAllQueueFeaturesExecutable uses Executable (sync-only) but declares queue lifecycle method tags(). This has no effect without the QueueableExecutable trait.',
                    12,
                ],
                [
                    'Class SyncWithAllQueueFeaturesExecutable uses Executable (sync-only) but declares queue property $tries. This has no effect without the QueueableExecutable trait.',
                    12,
                ],
                [
                    'Class SyncWithAllQueueFeaturesExecutable uses Executable (sync-only) but has attribute #[WithoutRelations]. This has no effect without the QueueableExecutable trait.',
                    12,
                ],
                [
                    'Class SyncWithAllQueueFeaturesExecutable uses Executable (sync-only) but implements ShouldBeEncrypted. This has no effect without the QueueableExecutable trait.',
                    12,
                ],
            ]
        );
    }
}
