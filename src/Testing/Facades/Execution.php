<?php

declare(strict_types=1);

namespace Havn\Executable\Testing\Facades;

use Closure;
use Havn\Executable\Jobs\ExecutableJob;
use Havn\Executable\Testing\ExecutionManager;
use Havn\Executable\Testing\Queueing\PushedBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void assertBatched(array<class-string|Closure>|callable $callback)
 * @method static void assertBatchCount(int $count)
 * @method static void assertNothingBatched()
 * @method static Collection<int, PushedBatch> batched(?callable $filter = null)
 * @method static void dumpJobs()
 * @method static void dumpBatches()
 * @method static void setTestingJob(ExecutableJob $job)
 * @method static ExecutableJob|null getTestingJob()
 *
 * @see ExecutionManager
 */
class Execution extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ExecutionManager::class;
    }
}
