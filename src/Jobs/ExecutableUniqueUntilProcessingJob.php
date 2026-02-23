<?php

declare(strict_types=1);

namespace Havn\Executable\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

/**
 * @internal
 */
final class ExecutableUniqueUntilProcessingJob extends ExecutableUniqueJob implements ShouldBeUniqueUntilProcessing {}
