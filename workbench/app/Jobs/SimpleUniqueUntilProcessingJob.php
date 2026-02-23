<?php

declare(strict_types=1);

namespace Workbench\App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SimpleUniqueUntilProcessingJob implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Batchable, InteractsWithQueue, Queueable;

    public function handle(): void
    {
        // ..
    }

    public function uniqueId(): string
    {
        return 'some-unique-id';
    }

    public function uniqueFor(): int
    {
        return 5;
    }
}
