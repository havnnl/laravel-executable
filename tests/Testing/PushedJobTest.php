<?php

declare(strict_types=1);

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Jobs\ExecutableJob;
use Havn\Executable\Jobs\ExecutableUniqueJob;
use Havn\Executable\Jobs\ExecutableUniqueUntilProcessingJob;
use Havn\Executable\Testing\Exceptions\CannotCheckArgumentsForJob;
use Havn\Executable\Testing\Queueing\PushedJob;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\VarDumper\VarDumper;
use Workbench\App\Executables\PlainQueueableExecutable;
use Workbench\App\Jobs\SimpleEncryptedJob;
use Workbench\App\Jobs\SimpleJob;
use Workbench\App\Jobs\SimpleUniqueJob;
use Workbench\App\Jobs\SimpleUniqueUntilProcessingJob;
use Workbench\App\Models\SomeModel;

function pushedJobExecutable(?QueueableConfig $config = null, array $arguments = [])
{
    return PushedJob::from((new ExecutableJob(new PlainQueueableExecutable, $arguments, $config)));
}

function pushedJobUniqueExecutable(?QueueableConfig $config = null)
{
    return PushedJob::from((new ExecutableUniqueJob(new PlainQueueableExecutable, [], $config)));
}

it('checks if executable matches by class name', function () {
    $sut = pushedJobExecutable();

    expect($sut->is(PlainQueueableExecutable::class))->toBeTrue()
        ->and($sut->is(SimpleJob::class))->toBeFalse();

    $sut->assertIs(PlainQueueableExecutable::class);

    expect(fn () => $sut->assertIs(SimpleJob::class))
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is not expected job.');
});

it('checks if job matches by class name', function () {
    $sut = PushedJob::from((new SimpleJob));

    expect($sut->is(SimpleJob::class))->toBeTrue()
        ->and($sut->is(PlainQueueableExecutable::class))->toBeFalse();

    $sut->assertIs(SimpleJob::class);

    expect(fn () => $sut->assertIs(PlainQueueableExecutable::class))
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] is not expected job.');
});

it('checks if executable matches by closure', function () {

    $sut = pushedJobExecutable();

    expect($sut->is(fn (PushedJob $job) => true))->toBeTrue()
        ->and($sut->is(fn (PushedJob $job) => false))->toBeFalse()
        ->and($sut->is(fn (PlainQueueableExecutable $job) => true))->toBeTrue()
        ->and($sut->is(fn (PlainQueueableExecutable $job) => false))->toBeFalse()
        ->and($sut->is(fn (SimpleJob $job) => true))->toBeFalse()
        ->and($sut->is(fn (SimpleJob $job) => false))->toBeFalse();

    $sut->assertIs(fn (PushedJob $job) => true);
    $sut->assertIs(fn (PlainQueueableExecutable $job) => true);

    expect(fn () => $sut->assertIs(fn (PushedJob $job) => false))
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is not expected job.');

    expect(fn () => $sut->assertIs(fn (PlainQueueableExecutable $job) => false))
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is not expected job.');

    expect(fn () => $sut->assertIs(fn (SimpleJob $job) => true))
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is not expected job.');

    expect(fn () => $sut->assertIs(fn (SimpleJob $job) => false))
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is not expected job.');
});

it('checks if job matches by closure', function () {

    $sut = PushedJob::from((new SimpleJob));

    expect($sut->is(fn (PushedJob $job) => true))->toBeTrue()
        ->and($sut->is(fn (PushedJob $job) => false))->toBeFalse()
        ->and($sut->is(fn (SimpleJob $job) => true))->toBeTrue()
        ->and($sut->is(fn (SimpleJob $job) => false))->toBeFalse()
        ->and($sut->is(fn (PlainQueueableExecutable $job) => true))->toBeFalse()
        ->and($sut->is(fn (PlainQueueableExecutable $job) => false))->toBeFalse();

    $sut->assertIs(fn (PushedJob $job) => true);
    $sut->assertIs(fn (SimpleJob $job) => true);

    expect(fn () => $sut->assertIs(fn (PushedJob $job) => false))
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] is not expected job.');

    expect(fn () => $sut->assertIs(fn (SimpleJob $job) => false))
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] is not expected job.');

    expect(fn () => $sut->assertIs(fn (PlainQueueableExecutable $job) => true))
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] is not expected job.');

    expect(fn () => $sut->assertIs(fn (PlainQueueableExecutable $job) => false))
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] is not expected job.');
});

it('checks if executable is on connection', function () {

    $sut = pushedJobExecutable(new QueueableConfig(connection: 'some-connection'));

    expect($sut->isOnConnection('some-connection'))->toBeTrue()
        ->and($sut->isOnConnection('some-other-connection'))->toBeFalse();

    $sut->assertIsOnConnection('some-connection');

    expect(fn () => $sut->assertIsOnConnection('some-other-connection'))
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is on connection [some-connection] instead of [some-other-connection]');
});

it('checks if job is on connection', function () {

    $sut = PushedJob::from((new SimpleJob)->onConnection('some-connection'));

    expect($sut->isOnConnection('some-connection'))->toBeTrue()
        ->and($sut->isOnConnection('some-other-connection'))->toBeFalse();

    $sut->assertIsOnConnection('some-connection');

    expect(fn () => $sut->assertIsOnConnection('some-other-connection'))
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] is on connection [some-connection] instead of [some-other-connection]');
});

it('checks if executable is on queue', function () {

    $sut = pushedJobExecutable(new QueueableConfig(queue: 'some-queue'));

    expect($sut->isOnQueue('some-queue'))->toBeTrue()
        ->and($sut->isOnQueue('some-other-queue'))->toBeFalse();

    $sut->assertIsOnQueue('some-queue');

    expect(fn () => $sut->assertIsOnQueue('some-other-queue'))
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is on queue [some-queue] instead of [some-other-queue]');
});

it('checks if job is on queue', function () {

    $sut = PushedJob::from((new SimpleJob)->onQueue('some-queue'));

    expect($sut->isOnQueue('some-queue'))->toBeTrue()
        ->and($sut->isOnQueue('some-other-queue'))->toBeFalse();

    $sut->assertIsOnQueue('some-queue');

    expect(fn () => $sut->assertIsOnQueue('some-other-queue'))
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] is on queue [some-queue] instead of [some-other-queue]');
});

it('checks if executable is encrypted', function () {

    $encrypted = pushedJobExecutable(new QueueableConfig(shouldBeEncrypted: true));
    $notEncrypted = pushedJobExecutable(new QueueableConfig(shouldBeEncrypted: false));

    expect($encrypted->isEncrypted())->toBeTrue()
        ->and($encrypted->isEncrypted(false))->toBeFalse()
        ->and($notEncrypted->isEncrypted())->toBeFalse()
        ->and($notEncrypted->isEncrypted(false))->toBeTrue();

    $encrypted->assertIsEncrypted();
    $notEncrypted->assertIsEncrypted(false);

    expect(fn () => $encrypted->assertIsEncrypted(false))
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is encrypted.');

    expect(fn () => $notEncrypted->assertIsEncrypted())
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is not encrypted.');
});

it('checks if job is encrypted', function () {

    $encrypted = PushedJob::from((new SimpleEncryptedJob));
    $notEncrypted = PushedJob::from((new SimpleJob));

    expect($encrypted->isEncrypted())->toBeTrue()
        ->and($encrypted->isEncrypted(false))->toBeFalse()
        ->and($notEncrypted->isEncrypted())->toBeFalse()
        ->and($notEncrypted->isEncrypted(false))->toBeTrue();

    $encrypted->assertIsEncrypted();
    $notEncrypted->assertIsEncrypted(false);

    expect(fn () => $encrypted->assertIsEncrypted(false))
        ->toThrow(ExpectationFailedException::class, '[SimpleEncryptedJob] is encrypted.');

    expect(fn () => $notEncrypted->assertIsEncrypted())
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] is not encrypted.');
});

it('checks if executable is unique', function () {

    $unique = pushedJobUniqueExecutable(new QueueableConfig(uniqueId: 'some-id'));
    $notUnique = pushedJobExecutable(new QueueableConfig);

    expect($unique->isUnique())->toBeTrue()
        ->and($unique->isUnique('some-id'))->toBeTrue()
        ->and($unique->isUnique('some-other-id'))->toBeFalse()
        ->and($notUnique->isUnique())->toBeFalse();

    $unique->assertIsUnique();
    $unique->assertIsUnique('some-id');

    expect(fn () => $unique->assertIsUnique('some-other-id'))
        ->toThrow(ExpectationFailedException::class, '[ExecutableUniqueJob] is unique but with different uniqueId.');

    expect(fn () => $notUnique->assertIsUnique())
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is not unique.');
});

it('checks if job is unique', function () {

    $unique = PushedJob::from((new SimpleUniqueJob));
    $notUnique = PushedJob::from((new SimpleJob));

    expect($unique->isUnique())->toBeTrue()
        ->and($unique->isUnique('some-unique-id'))->toBeTrue()
        ->and($unique->isUnique('some-other-unique-id'))->toBeFalse()
        ->and($notUnique->isUnique())->toBeFalse();

    $unique->assertIsUnique();
    $unique->assertIsUnique('some-unique-id');

    expect(fn () => $unique->assertIsUnique('some-other-id'))
        ->toThrow(ExpectationFailedException::class, '[SimpleUniqueJob] is unique but with different uniqueId.');

    expect(fn () => $notUnique->assertIsUnique())
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] is not unique.');
});

it('checks if executable is unique for', function () {

    $unique = pushedJobUniqueExecutable(new QueueableConfig(uniqueFor: 5));
    $notUnique = pushedJobExecutable(new QueueableConfig(uniqueFor: 5));

    expect($unique->isUniqueFor(5))->toBeTrue()
        ->and($unique->isUniqueFor(10))->toBeFalse()
        ->and($notUnique->isUniqueFor(5))->toBeFalse();

    $unique->assertIsUniqueFor(5);

    expect(fn () => $unique->assertIsUniqueFor(10))
        ->toThrow(ExpectationFailedException::class, '[ExecutableUniqueJob] is unique but for different duration.');

    expect(fn () => $notUnique->assertIsUniqueFor(5))
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is not unique.');
});

it('checks if job is unique for', function () {

    $unique = PushedJob::from((new SimpleUniqueJob));
    $notUnique = PushedJob::from((new SimpleJob));

    expect($unique->isUniqueFor(5))->toBeTrue()
        ->and($unique->isUniqueFor(10))->toBeFalse()
        ->and($notUnique->isUniqueFor(5))->toBeFalse();

    $unique->assertIsUniqueFor(5);

    expect(fn () => $unique->assertIsUniqueFor(10))
        ->toThrow(ExpectationFailedException::class, '[SimpleUniqueJob] is unique but for different duration.');

    expect(fn () => $notUnique->assertIsUniqueFor(5))
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] is not unique.');
});

it('checks if executable is unique until processing', function () {

    $unique = PushedJob::from((new ExecutableUniqueUntilProcessingJob(new PlainQueueableExecutable, [], new QueueableConfig(uniqueId: 'some-id'))));
    $notUnique = pushedJobExecutable(new QueueableConfig);

    expect($unique->isUniqueUntilProcessing())->toBeTrue()
        ->and($unique->isUniqueUntilProcessing('some-id'))->toBeTrue()
        ->and($unique->isUniqueUntilProcessing('some-other-id'))->toBeFalse()
        ->and($notUnique->isUniqueUntilProcessing())->toBeFalse();

    $unique->assertIsUniqueUntilProcessing();
    $unique->assertIsUniqueUntilProcessing('some-id');

    expect(fn () => $unique->assertIsUniqueUntilProcessing('some-other-id'))
        ->toThrow(ExpectationFailedException::class, '[ExecutableUniqueUntilProcessingJob] is unique until processing but with different uniqueId.');

    expect(fn () => $notUnique->assertIsUniqueUntilProcessing())
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is not unique until processing.');
});

it('checks if job is unique until processing', function () {

    $unique = PushedJob::from((new SimpleUniqueUntilProcessingJob));
    $notUnique = PushedJob::from((new SimpleJob));

    expect($unique->isUniqueUntilProcessing())->toBeTrue()
        ->and($unique->isUniqueUntilProcessing('some-unique-id'))->toBeTrue()
        ->and($unique->isUniqueUntilProcessing('some-other-unique-id'))->toBeFalse()
        ->and($notUnique->isUniqueUntilProcessing())->toBeFalse();

    $unique->assertIsUniqueUntilProcessing();
    $unique->assertIsUniqueUntilProcessing('some-unique-id');

    expect(fn () => $unique->assertIsUniqueUntilProcessing('some-other-id'))
        ->toThrow(ExpectationFailedException::class, '[SimpleUniqueUntilProcessingJob] is unique until processing but with different uniqueId.');

    expect(fn () => $notUnique->assertIsUniqueUntilProcessing())
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] is not unique until processing.');
});

it('checks if executable has unique ID', function () {

    $uniqueId = pushedJobUniqueExecutable(new QueueableConfig(uniqueId: 'some-id'));
    $noUniqueId = pushedJobUniqueExecutable(new QueueableConfig);

    expect($uniqueId->hasUniqueId())->toBeTrue()
        ->and($uniqueId->hasUniqueId('some-id'))->toBeTrue()
        ->and($uniqueId->hasUniqueId('some-other-id'))->toBeFalse()
        ->and($noUniqueId->hasUniqueId())->toBeFalse();

    $uniqueId->assertHasUniqueId();
    $uniqueId->assertHasUniqueId('some-id');

    expect(fn () => $uniqueId->assertHasUniqueId('some-other-id'))
        ->toThrow(ExpectationFailedException::class, '[ExecutableUniqueJob] has a different uniqueId than expected.');

    expect(fn () => $noUniqueId->assertHasUniqueId())
        ->toThrow(ExpectationFailedException::class, '[ExecutableUniqueJob] does not have uniqueId.');
});

it('checks if job has unique ID', function () {

    $uniqueId = PushedJob::from((new SimpleUniqueUntilProcessingJob));
    $noUniqueId = PushedJob::from((new SimpleJob));

    expect($uniqueId->isUniqueUntilProcessing())->toBeTrue()
        ->and($uniqueId->isUniqueUntilProcessing('some-unique-id'))->toBeTrue()
        ->and($uniqueId->isUniqueUntilProcessing('some-other-unique-id'))->toBeFalse()
        ->and($noUniqueId->isUniqueUntilProcessing())->toBeFalse();

    $uniqueId->assertHasUniqueId();
    $uniqueId->assertHasUniqueId('some-unique-id');

    expect(fn () => $uniqueId->assertHasUniqueId('some-other-id'))
        ->toThrow(ExpectationFailedException::class, '[SimpleUniqueUntilProcessingJob] has a different uniqueId than expected.');

    expect(fn () => $noUniqueId->assertHasUniqueId())
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] does not have uniqueId.');
});

it('checks if executable is delayed', function () {

    $delayed = pushedJobExecutable(new QueueableConfig(delay: 5));
    $notDelayed = pushedJobExecutable(new QueueableConfig);

    expect($delayed->isDelayed())->toBeTrue()
        ->and($delayed->isDelayed(5))->toBeTrue()
        ->and($delayed->isDelayed(10))->toBeFalse()
        ->and($notDelayed->isDelayed())->toBeFalse();

    $delayed->assertIsDelayed();
    $delayed->assertIsDelayed(5);

    expect(fn () => $delayed->assertIsDelayed(10))
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is delayed but with a different duration.');

    expect(fn () => $notDelayed->assertIsDelayed())
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is not delayed.');
});

it('checks if job is delayed', function () {

    $delayed = PushedJob::from((new SimpleJob)->delay(5));
    $notDelayed = PushedJob::from((new SimpleJob));

    expect($delayed->isDelayed())->toBeTrue()
        ->and($delayed->isDelayed(5))->toBeTrue()
        ->and($delayed->isDelayed(10))->toBeFalse()
        ->and($notDelayed->isDelayed())->toBeFalse();

    $delayed->assertIsDelayed();
    $delayed->assertIsDelayed(5);

    expect(fn () => $delayed->assertIsDelayed(10))
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] is delayed but with a different duration.');

    expect(fn () => $notDelayed->assertIsDelayed())
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] is not delayed.');
});

it('checks if executable is executed with arguments', function () {

    $sut = pushedJobExecutable(arguments: ['arg1', 'arg2']);

    expect($sut->executedWith('arg1', 'arg2'))->toBeTrue()
        ->and($sut->executedWith('arg2', 'arg1'))->toBeFalse()
        ->and($sut->executedWith('arg1'))->toBeFalse();

    $sut->assertExecutedWith('arg1', 'arg2');

    expect(fn () => $sut->assertExecutedWith('arg2', 'arg1'))
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is not executed with expected arguments.');
});

it('checks if executable is executed with arguments, matching model by key', function () {

    $sut = pushedJobExecutable(arguments: [
        new SomeModel(['id' => 123, 'value' => 'original']),
        new SomeModel(['id' => 456, 'value' => 'original']),
    ]);

    expect($sut->executedWith(
        new SomeModel(['id' => 123, 'value' => 'changed']),
        new SomeModel(['id' => 456, 'value' => 'changed']),
    ))->toBeTrue()
        ->and($sut->executedWith(
            new SomeModel(['id' => 456, 'value' => 'changed']),
            new SomeModel(['id' => 123, 'value' => 'changed']),
        ))->toBeFalse()
        ->and($sut->executedWith(
            new SomeModel(['id' => 123, 'value' => 'changed']),
            new SomeModel(['id' => 789, 'value' => 'changed']),
        ))->toBeFalse();

    $sut->assertExecutedWith(
        new SomeModel(['id' => 123, 'value' => 'changed']),
        new SomeModel(['id' => 456, 'value' => 'changed'])
    );

    expect(fn () => $sut->assertExecutedWith(
        new SomeModel(['id' => 456, 'value' => 'changed']),
        new SomeModel(['id' => 123, 'value' => 'changed'])
    ))->toThrow(ExpectationFailedException::class, '[ExecutableJob] is not executed with expected arguments.');
});

it('throws if checking arguments for regular job', function () {

    $sut = PushedJob::from((new SimpleJob));

    $sut->executedWith(fn () => true);

})->throws(CannotCheckArgumentsForJob::class);

it('throws if checking arguments with closure for regular job', function () {

    $sut = PushedJob::from((new SimpleJob));

    $sut->executedWithArgs(fn () => true);

})->throws(CannotCheckArgumentsForJob::class);

it('checks if executable is executed with arguments using callback', function () {

    $sut = pushedJobExecutable(arguments: [123, 456]);

    expect($sut->executedWithArgs(function ($arg1, $arg2) {
        return $arg1 == 123 && $arg2 == 456;
    }))->toBeTrue()
        ->and($sut->executedWithArgs(fn () => false))->toBeFalse();

    $sut->assertExecutedWithArgs(fn ($arg1, $arg2) => $arg1 == 123 && $arg2 == 456);

    expect(fn () => $sut->assertExecutedWithArgs(fn ($arg1, $arg2) => $arg1 == 456 && $arg2 == 123))
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] is not executed with expected arguments.');
});

it('throws if checking arguments for regular job using callback', function () {
    $sut = PushedJob::from((new SimpleJob));

    $sut->executedWithArgs(fn () => true);
})->throws(CannotCheckArgumentsForJob::class);

it('checks if executable has chain', function () {
    $sut = PushedJob::from((new ExecutableJob(new PlainQueueableExecutable, [], null))
        ->appendToChain(new SimpleEncryptedJob)
        ->appendToChain(PlainQueueableExecutable::prepare()->execute()));

    expect($sut->hasChain([SimpleEncryptedJob::class, PlainQueueableExecutable::class]))->toBeTrue()
        ->and($sut->hasChain([PlainQueueableExecutable::class, SimpleEncryptedJob::class]))->toBeFalse()
        ->and($sut->hasChain([
            fn (SimpleEncryptedJob $job) => true,
            fn (PlainQueueableExecutable $job) => true,
        ]))->toBeTrue()
        ->and($sut->hasChain([
            fn (PushedJob $job) => true,
            fn (PushedJob $job) => true,
        ]))->toBeTrue()
        ->and($sut->hasChain([
            fn (PlainQueueableExecutable $job) => true,
            fn (SimpleEncryptedJob $job) => true,
        ]))->toBeFalse();

    $sut->assertHasChain();
    $sut->assertHasChain([SimpleEncryptedJob::class, PlainQueueableExecutable::class]);

    expect(fn () => $sut->assertHasChain([PlainQueueableExecutable::class, SimpleEncryptedJob::class]))
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] has a chain but with different jobs.');
});

it('checks if job has chain', function () {
    $sut = PushedJob::from((new SimpleJob)
        ->appendToChain(new SimpleEncryptedJob)
        ->appendToChain(PlainQueueableExecutable::prepare()->execute()));

    expect($sut->hasChain([SimpleEncryptedJob::class, PlainQueueableExecutable::class]))->toBeTrue()
        ->and($sut->hasChain([PlainQueueableExecutable::class, SimpleEncryptedJob::class]))->toBeFalse()
        ->and($sut->hasChain([
            fn (SimpleEncryptedJob $job) => true,
            fn (PlainQueueableExecutable $job) => true,
        ]))->toBeTrue()
        ->and($sut->hasChain([
            fn (PushedJob $job) => true,
            fn (PushedJob $job) => true,
        ]))->toBeTrue()
        ->and($sut->hasChain([
            fn (PlainQueueableExecutable $job) => true,
            fn (SimpleEncryptedJob $job) => true,
        ]))->toBeFalse();

    $sut->assertHasChain();
    $sut->assertHasChain([SimpleEncryptedJob::class, PlainQueueableExecutable::class]);

    expect(fn () => $sut->assertHasChain([PlainQueueableExecutable::class, SimpleEncryptedJob::class]))
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] has a chain but with different jobs.');
});

it('checks if executable has no chain', function () {
    $withChain = PushedJob::from((new ExecutableJob(new PlainQueueableExecutable, [], null))
        ->appendToChain(new SimpleEncryptedJob));
    $withoutChain = PushedJob::from((new ExecutableJob(new PlainQueueableExecutable, [], null)));

    expect($withChain->hasNoChain())->toBeFalse()
        ->and($withoutChain->hasNoChain())->toBeTrue();

    $withoutChain->assertHasNoChain();

    expect(fn () => $withChain->assertHasNoChain())
        ->toThrow(ExpectationFailedException::class, '[ExecutableJob] does have a chain.');
});

it('checks if job has no chain', function () {
    $withChain = PushedJob::from((new SimpleJob)
        ->appendToChain(new SimpleEncryptedJob));
    $withoutChain = PushedJob::from((new SimpleJob));

    expect($withChain->hasNoChain())->toBeFalse()
        ->and($withoutChain->hasNoChain())->toBeTrue();

    $withoutChain->assertHasNoChain();

    expect(fn () => $withChain->assertHasNoChain())
        ->toThrow(ExpectationFailedException::class, '[SimpleJob] does have a chain.');
});

it('creates summary of job and chain', function () {
    $sut = PushedJob::from((new SimpleJob)
        ->appendToChain(new SimpleEncryptedJob)
        ->appendToChain(PlainQueueableExecutable::prepare()->execute('some input')));

    expect($sut->summary())->toEqual([
        'job' => SimpleJob::class,
        'chain' => [
            [
                'job' => SimpleEncryptedJob::class,
                'chain' => [],
            ],
            [
                'executable' => PlainQueueableExecutable::class,
                'arguments' => ['input' => 'some input'],
                'chain' => [],
            ],
        ],
    ]);
});

it('outputs job summary on dump', function () {
    $dumpedValues = [];

    VarDumper::setHandler(function ($value) use (&$dumpedValues) {
        $dumpedValues[] = $value;
    });

    $job = PushedJob::from(new SimpleJob);

    $job->dump();

    expect($dumpedValues)->toHaveCount(1)
        ->and($dumpedValues[0])->toBe($job->summary());
});

it('checks if job has middleware by string', function () {
    $job = new class
    {
        public function middleware()
        {
            return [
                'Workbench\App\Middleware\FlagServerVariableMiddleware',
                'Workbench\App\Middleware\PushToServerVariableMiddleware',
            ];
        }
    };

    $sut = PushedJob::from($job);

    expect($sut->hasMiddleware('Workbench\App\Middleware\FlagServerVariableMiddleware'))->toBeTrue()
        ->and($sut->hasMiddleware('Workbench\App\Middleware\PushToServerVariableMiddleware'))->toBeTrue()
        ->and($sut->hasMiddleware('NonExistentMiddleware'))->toBeFalse();
});

it('checks if job has middleware by callable', function () {
    $job = new class
    {
        public function middleware()
        {
            return [
                'Workbench\App\Middleware\FlagServerVariableMiddleware',
                'Workbench\App\Middleware\PushToServerVariableMiddleware',
            ];
        }
    };

    $sut = PushedJob::from($job);

    expect($sut->hasMiddleware(fn ($m) => str_contains($m, 'Flag')))->toBeTrue()
        ->and($sut->hasMiddleware(fn ($m) => str_contains($m, 'NonExistent')))->toBeFalse();
});

it('returns false when job does not have middleware method', function () {
    $job = new class {};

    $sut = PushedJob::from($job);

    expect($sut->hasMiddleware('SomeMiddleware'))->toBeFalse();
});

it('checks if job has middleware on property', function () {
    $job = new class
    {
        public $middleware = [
            'Workbench\App\Middleware\FlagServerVariableMiddleware',
            'Workbench\App\Middleware\PushToServerVariableMiddleware',
        ];
    };

    $sut = PushedJob::from($job);

    expect($sut->hasMiddleware('Workbench\App\Middleware\FlagServerVariableMiddleware'))->toBeTrue()
        ->and($sut->hasMiddleware('Workbench\App\Middleware\PushToServerVariableMiddleware'))->toBeTrue()
        ->and($sut->hasMiddleware('NonExistentMiddleware'))->toBeFalse();
});

it('merges middleware from property and method in correct order', function () {
    $job = new class
    {
        public $middleware = [
            'PropertyMiddleware1',
            'PropertyMiddleware2',
        ];

        public function middleware()
        {
            return [
                'MethodMiddleware1',
                'MethodMiddleware2',
            ];
        }
    };

    $sut = PushedJob::from($job);

    // All should be present (merged)
    expect($sut->hasMiddleware('PropertyMiddleware1'))->toBeTrue()
        ->and($sut->hasMiddleware('PropertyMiddleware2'))->toBeTrue()
        ->and($sut->hasMiddleware('MethodMiddleware1'))->toBeTrue()
        ->and($sut->hasMiddleware('MethodMiddleware2'))->toBeTrue();
});

it('checks if job has any middleware', function () {
    $job = new class
    {
        public function middleware()
        {
            return [
                'SomeMiddleware',
            ];
        }
    };

    $sut = PushedJob::from($job);

    expect($sut->hasMiddleware())->toBeTrue();
});

it('returns false when checking for any middleware on job without middleware', function () {
    $job = new class {};

    $sut = PushedJob::from($job);

    expect($sut->hasMiddleware())->toBeFalse();
});

it('asserts job has middleware', function () {
    $job = new class
    {
        public function middleware()
        {
            return [
                'Workbench\App\Middleware\FlagServerVariableMiddleware',
            ];
        }
    };

    $sut = PushedJob::from($job);

    $result = $sut->assertHasMiddleware('Workbench\App\Middleware\FlagServerVariableMiddleware');

    expect($result)->toBe($sut);
});

it('fails asserting job has middleware', function () {
    $job = new class
    {
        public function middleware()
        {
            return [];
        }
    };

    $sut = PushedJob::from($job);

    expect(fn () => $sut->assertHasMiddleware('NonExistentMiddleware'))
        ->toThrow(ExpectationFailedException::class);
});

it('asserts job has any middleware', function () {
    $job = new class
    {
        public function middleware()
        {
            return [
                'SomeMiddleware',
            ];
        }
    };

    $sut = PushedJob::from($job);

    $result = $sut->assertHasMiddleware();

    expect($result)->toBe($sut);
});

it('fails asserting job has any middleware', function () {
    $job = new class {};

    $sut = PushedJob::from($job);

    expect(fn () => $sut->assertHasMiddleware())
        ->toThrow(ExpectationFailedException::class);
});

it('checks if job has any tags', function () {
    $job = new class
    {
        public function tags()
        {
            return ['tag-1', 'tag-2'];
        }
    };

    $sut = PushedJob::from($job);

    expect($sut->hasTags())->toBeTrue();
});

it('returns false when job has no tags', function () {
    $job = new class
    {
        public function tags()
        {
            return null;
        }
    };

    $sut = PushedJob::from($job);

    expect($sut->hasTags())->toBeFalse();
});

it('returns false when job has empty tags array', function () {
    $job = new class
    {
        public function tags()
        {
            return [];
        }
    };

    $sut = PushedJob::from($job);

    expect($sut->hasTags())->toBeFalse();
});

it('checks if job has specific tags', function () {
    $job = new class
    {
        public function tags()
        {
            return ['tag-1', 'tag-2', 'tag-3'];
        }
    };

    $sut = PushedJob::from($job);

    expect($sut->hasTags(['tag-1', 'tag-2', 'tag-3']))->toBeTrue()
        ->and($sut->hasTags(['tag-3', 'tag-1', 'tag-2']))->toBeTrue() // Order doesn't matter
        ->and($sut->hasTags(['tag-1', 'tag-2']))->toBeFalse() // Missing tag-3
        ->and($sut->hasTags(['tag-1', 'tag-4']))->toBeFalse(); // Has wrong tag
});

it('returns false when job does not have tags method', function () {
    $job = new class {};

    $sut = PushedJob::from($job);

    expect($sut->hasTags())->toBeFalse();
});

it('asserts job has any tags', function () {
    $job = new class
    {
        public function tags()
        {
            return ['tag-1'];
        }
    };

    $sut = PushedJob::from($job);

    $result = $sut->assertHasTags();

    expect($result)->toBe($sut);
});

it('fails asserting job has any tags when none exist', function () {
    $job = new class
    {
        public function tags()
        {
            return null;
        }
    };

    $sut = PushedJob::from($job);

    expect(fn () => $sut->assertHasTags())
        ->toThrow(ExpectationFailedException::class);
});

it('asserts job has specific tags', function () {
    $job = new class
    {
        public function tags()
        {
            return ['tag-2', 'tag-1'];
        }
    };

    $sut = PushedJob::from($job);

    $result = $sut->assertHasTags(['tag-1', 'tag-2']);

    expect($result)->toBe($sut);
});

it('fails asserting job has specific tags', function () {
    $job = new class
    {
        public function tags()
        {
            return ['tag-1', 'tag-2'];
        }
    };

    $sut = PushedJob::from($job);

    expect(fn () => $sut->assertHasTags(['tag-1', 'tag-3']))
        ->toThrow(ExpectationFailedException::class);
});
