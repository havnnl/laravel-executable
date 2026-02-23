<?php

declare(strict_types=1);

namespace Workbench\App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SimpleEncryptedJob implements ShouldBeEncrypted, ShouldQueue
{
    use Batchable, InteractsWithQueue, Queueable;

    public function handle(): void
    {
        // ..
    }
}
