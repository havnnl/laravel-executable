<?php

declare(strict_types=1);

use Havn\Executable\Jobs\ExecutableJob;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Executables\Configuration\MiddlewareMergingExecutable;
use Workbench\App\Middleware\FlagServerVariableMiddleware;
use Workbench\App\Middleware\PushToServerVariableMiddleware;

beforeEach(function () {
    Queue::fake();
});

it('sets middleware from method and property', function () {
    MiddlewareMergingExecutable::onQueue()->execute();

    Queue::assertPushed(function (ExecutableJob $job) {
        return expect($job->middleware)->toHaveCount(1)
            ->and($job->middleware)->toContain(FlagServerVariableMiddleware::class)
            ->and($job->middleware())->toHaveCount(1)
            ->and($job->middleware())->toContain(PushToServerVariableMiddleware::class);
    });
});
