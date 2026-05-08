<?php

declare(strict_types=1);

namespace Havn\Executable\Tests\Analysis;

use PHPStan\Testing\TypeInferenceTestCase;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Workbench\App\Executables\Analysis\ExecuteReturnTypeFixture;

final class ExecuteReturnTypeExtensionTest extends TypeInferenceTestCase
{
    /** @see ExecuteReturnTypeFixture */
    #[WithoutErrorHandler]
    public function test_execute_return_types(): void
    {
        $asserts = self::gatherAssertTypes(
            __DIR__.'./../../workbench/app/Executables/Analysis/ExecuteReturnTypeFixture.php'
        );

        $this->assertNotEmpty($asserts, 'No assertType() calls found in fixture');

        foreach ($asserts as $args) {
            $this->assertFileAsserts(...$args);
        }
    }

    /**
     * @return list<string>
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__.'/../../extension.neon',
        ];
    }
}
