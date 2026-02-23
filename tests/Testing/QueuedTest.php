<?php

declare(strict_types=1);

use Havn\Executable\Testing\Queueing\PushedJob;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\VarDumper\VarDumper;
use Workbench\App\Executables\PlainQueueableExecutable;

describe('queued basic functionality', function () {
    beforeEach(function () {
        Queue::fake();
    });

    it('passes when executable was queued', function () {
        PlainQueueableExecutable::onQueue()->execute('input');

        PlainQueueableExecutable::assert()->queued();
    });

    it('fails when executable was not queued', function () {
        expect(fn () => PlainQueueableExecutable::assert()->queued())
            ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was not queued.');
    });
});

describe('notQueued', function () {
    beforeEach(function () {
        Queue::fake();
    });

    it('passes when executable was not queued', function () {
        PlainQueueableExecutable::assert()->notQueued();
    });

    it('fails when executable was queued', function () {
        PlainQueueableExecutable::onQueue()->execute('input');

        expect(fn () => PlainQueueableExecutable::assert()->notQueued())
            ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was queued [1] times instead of [0] times.');
    });

    it('fails when executable was queued multiple times', function () {
        PlainQueueableExecutable::onQueue()->execute('input1');
        PlainQueueableExecutable::onQueue()->execute('input2');

        expect(fn () => PlainQueueableExecutable::assert()->notQueued())
            ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was queued [2] times instead of [0] times.');
    });
});

describe('count methods', function () {
    beforeEach(function () {
        Queue::fake();
    });

    describe('never()', function () {
        it('passes when not queued', function () {
            PlainQueueableExecutable::assert()->queued()->never();
        });

        it('fails when queued once', function () {
            PlainQueueableExecutable::onQueue()->execute('input');

            $assertion = PlainQueueableExecutable::assert()->queued()->never();

            expect(fn () => $assertion->__destruct())
                ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was queued [1] times instead of [0] times.');
        });

        it('can be combined with filters', function () {
            PlainQueueableExecutable::onQueue('high-priority')->execute('input1');

            PlainQueueableExecutable::assert()->queued()->onQueue('low-priority')->never();
        });
    });

    describe('once()', function () {
        it('passes when queued exactly once', function () {
            PlainQueueableExecutable::onQueue()->execute('input');

            PlainQueueableExecutable::assert()->queued()->once();
        });

        it('fails when not queued', function () {
            expect(fn () => PlainQueueableExecutable::assert()->queued()->once())
                ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was queued [0] times instead of [1] times.');
        });

        it('fails when queued twice', function () {
            PlainQueueableExecutable::onQueue()->execute('input1');
            PlainQueueableExecutable::onQueue()->execute('input2');

            expect(fn () => PlainQueueableExecutable::assert()->queued()->once())
                ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was queued [2] times instead of [1] times.');
        });
    });

    describe('twice()', function () {
        it('passes when queued exactly twice', function () {
            PlainQueueableExecutable::onQueue()->execute('input1');
            PlainQueueableExecutable::onQueue()->execute('input2');

            PlainQueueableExecutable::assert()->queued()->twice();
        });

        it('fails when queued once', function () {
            PlainQueueableExecutable::onQueue()->execute('input');

            expect(fn () => PlainQueueableExecutable::assert()->queued()->twice())
                ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was queued [1] times instead of [2] times.');
        });
    });

    describe('times()', function () {
        it('passes when queued exact number of times', function () {
            PlainQueueableExecutable::onQueue()->execute('input1');
            PlainQueueableExecutable::onQueue()->execute('input2');
            PlainQueueableExecutable::onQueue()->execute('input3');

            PlainQueueableExecutable::assert()->queued()->times(3);
        });

        it('passes when asserting zero times and not queued', function () {
            PlainQueueableExecutable::assert()->queued()->times(0);
        });

        it('fails when count does not match', function () {
            PlainQueueableExecutable::onQueue()->execute('input1');
            PlainQueueableExecutable::onQueue()->execute('input2');

            expect(fn () => PlainQueueableExecutable::assert()->queued()->times(5))
                ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was queued [2] times instead of [5] times.');
        });
    });
});

describe('argument filtering with with()', function () {
    beforeEach(function () {
        Queue::fake();
    });

    it('passes when queued with exact arguments', function () {
        PlainQueueableExecutable::onQueue()->execute('input');

        PlainQueueableExecutable::assert()->queued()->with('input');
    });

    it('passes when queued with exact arguments and count', function () {
        PlainQueueableExecutable::onQueue()->execute('input');

        PlainQueueableExecutable::assert()->queued()->with('input')->once();
    });

    it('fails when not queued with specified arguments', function () {
        PlainQueueableExecutable::onQueue()->execute('other-input');

        $assertion = PlainQueueableExecutable::assert()->queued()->with('input');

        expect(fn () => $assertion->__destruct())
            ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was not queued with specific arguments.');
    });

    it('fails when queued with different arguments', function () {
        PlainQueueableExecutable::onQueue()->execute('input1');
        PlainQueueableExecutable::onQueue()->execute('input2');

        $assertion = PlainQueueableExecutable::assert()->queued()->with('input1')->twice();

        expect(fn () => $assertion->__destruct())
            ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was queued [1] times instead of [2] times with specific arguments.');
    });

    it('passes when queued multiple times with same arguments', function () {
        PlainQueueableExecutable::onQueue()->execute('input');
        PlainQueueableExecutable::onQueue()->execute('input');

        PlainQueueableExecutable::assert()->queued()->with('input')->twice();
    });
});

describe('argument filtering with withArgs()', function () {
    beforeEach(function () {
        Queue::fake();
    });

    it('passes when arguments match callback', function () {
        PlainQueueableExecutable::onQueue()->execute('input');

        PlainQueueableExecutable::assert()->queued()->withArgs(fn ($arg) => $arg === 'input');
    });

    it('passes when arguments match callback with count', function () {
        PlainQueueableExecutable::onQueue()->execute('input');

        PlainQueueableExecutable::assert()->queued()->withArgs(fn ($arg) => $arg === 'input')->once();
    });

    it('fails when arguments do not match callback', function () {
        PlainQueueableExecutable::onQueue()->execute('other-input');

        $assertion = PlainQueueableExecutable::assert()->queued()->withArgs(fn ($arg) => $arg === 'input');

        expect(fn () => $assertion->__destruct())
            ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was not queued with specific arguments.');
    });

    it('filters correctly by callback', function () {
        PlainQueueableExecutable::onQueue()->execute('input1');
        PlainQueueableExecutable::onQueue()->execute('input2');
        PlainQueueableExecutable::onQueue()->execute('input1');

        PlainQueueableExecutable::assert()->queued()->withArgs(fn ($arg) => $arg === 'input1')->twice();
    });
});

describe('queue filtering with on()', function () {
    beforeEach(function () {
        Queue::fake();
    });

    it('passes when queued on specified queue', function () {
        PlainQueueableExecutable::onQueue('high-priority')->execute('input');

        PlainQueueableExecutable::assert()->queued()->onQueue('high-priority');
    });

    it('passes when queued on specified queue with count', function () {
        PlainQueueableExecutable::onQueue('high-priority')->execute('input');

        PlainQueueableExecutable::assert()->queued()->onQueue('high-priority')->once();
    });

    it('fails when not queued on specified queue', function () {
        PlainQueueableExecutable::onQueue('low-priority')->execute('input');

        expect(fn () => PlainQueueableExecutable::assert()->queued()->onQueue('high-priority'))
            ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was not queued on queue [high-priority].');
    });

    it('filters correctly by queue name', function () {
        PlainQueueableExecutable::onQueue('high-priority')->execute('input1');
        PlainQueueableExecutable::onQueue('low-priority')->execute('input2');
        PlainQueueableExecutable::onQueue('high-priority')->execute('input3');

        PlainQueueableExecutable::assert()->queued()->onQueue('high-priority')->twice();
    });
});

describe('chain filtering with withChain()', function () {
    beforeEach(function () {
        Queue::fake();
    });

    it('passes when queued with specified chain', function () {
        PlainQueueableExecutable::onQueue()
            ->chain([
                PlainQueueableExecutable::prepare()->execute('chained1'),
                PlainQueueableExecutable::prepare()->execute('chained2'),
            ])
            ->execute('input');

        PlainQueueableExecutable::assert()->queued()->withChain([
            PlainQueueableExecutable::class,
            PlainQueueableExecutable::class,
        ]);
    });

    it('passes when queued with chain and count', function () {
        PlainQueueableExecutable::onQueue()
            ->chain([
                PlainQueueableExecutable::prepare()->execute('chained'),
            ])
            ->execute('input');

        PlainQueueableExecutable::assert()->queued()->withChain([
            PlainQueueableExecutable::class,
        ])->once();
    });

    it('fails when not queued with chain', function () {
        PlainQueueableExecutable::onQueue()->execute('input');

        expect(fn () => PlainQueueableExecutable::assert()->queued()->withChain([
            PlainQueueableExecutable::class,
        ]))
            ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was not queued with chain.');
    });

    it('fails when chain does not match', function () {
        PlainQueueableExecutable::onQueue()
            ->chain([
                PlainQueueableExecutable::prepare()->execute('chained1'),
            ])
            ->execute('input');

        expect(fn () => PlainQueueableExecutable::assert()->queued()->withChain([
            PlainQueueableExecutable::class,
            PlainQueueableExecutable::class,
        ]))
            ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was not queued with chain.');
    });
});

describe('combined filters', function () {
    beforeEach(function () {
        Queue::fake();
    });

    it('can combine with() and on()', function () {
        PlainQueueableExecutable::onQueue('high-priority')->execute('input1');
        PlainQueueableExecutable::onQueue('low-priority')->execute('input1');
        PlainQueueableExecutable::onQueue('high-priority')->execute('input2');

        PlainQueueableExecutable::assert()
            ->queued()
            ->onQueue('high-priority')
            ->with('input1')
            ->once();
    });

    it('can combine withArgs() and on()', function () {
        PlainQueueableExecutable::onQueue('high-priority')->execute('input1');
        PlainQueueableExecutable::onQueue('low-priority')->execute('input1');

        PlainQueueableExecutable::assert()
            ->queued()
            ->onQueue('high-priority')
            ->withArgs(fn ($arg) => $arg === 'input1')
            ->once();
    });

    it('can combine with(), on(), and withChain()', function () {
        PlainQueueableExecutable::onQueue('high-priority')
            ->chain([PlainQueueableExecutable::prepare()->execute('chained')])
            ->execute('input1');

        PlainQueueableExecutable::onQueue('low-priority')
            ->chain([PlainQueueableExecutable::prepare()->execute('chained')])
            ->execute('input1');

        PlainQueueableExecutable::assert()
            ->queued()
            ->onQueue('high-priority')
            ->with('input1')
            ->withChain([PlainQueueableExecutable::class])
            ->once();
    });

    it('fails when combined filters do not all match', function () {
        PlainQueueableExecutable::onQueue('high-priority')->execute('input1');
        PlainQueueableExecutable::onQueue('low-priority')->execute('input2');

        $assertion = PlainQueueableExecutable::assert()
            ->queued()
            ->onQueue('high-priority')
            ->with('input2')
            ->once();

        expect(fn () => $assertion->__destruct())
            ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was queued [0] times instead of [1] times with specific arguments on queue [high-priority].');
    });
});

describe('flexible chaining order', function () {
    beforeEach(function () {
        Queue::fake();
    });

    it('allows count before filters', function () {
        PlainQueueableExecutable::onQueue()->execute('input1');

        PlainQueueableExecutable::assert()->queued()->once()->with('input1');
    });

    it('allows filters before count', function () {
        PlainQueueableExecutable::onQueue()->execute('input1');

        PlainQueueableExecutable::assert()->queued()->with('input1')->once();
    });

    it('allows count in the middle of filters', function () {
        PlainQueueableExecutable::onQueue('high-priority')->execute('input1');

        PlainQueueableExecutable::assert()->queued()->onQueue('high-priority')->once()->with('input1');
    });

    it('allows multiple filters after count', function () {
        PlainQueueableExecutable::onQueue('high-priority')->execute('input1');

        PlainQueueableExecutable::assert()->queued()->once()->onQueue('high-priority')->with('input1');
    });

    it('verifies count with filters regardless of order', function () {
        PlainQueueableExecutable::onQueue('high-priority')->execute('input1');
        PlainQueueableExecutable::onQueue('low-priority')->execute('input2');

        // Count before filters
        PlainQueueableExecutable::assert()->queued()->once()->onQueue('high-priority')->with('input1');

        // Filters before count
        PlainQueueableExecutable::assert()->queued()->onQueue('low-priority')->with('input2')->once();
    });

    it('fails with correct error when count is before filters', function () {
        PlainQueueableExecutable::onQueue('high-priority')->execute('input1');

        $assertion = PlainQueueableExecutable::assert()->queued()->twice()->onQueue('high-priority')->with('input1');

        expect(fn () => $assertion->__destruct())
            ->toThrow(ExpectationFailedException::class, '[PlainQueueableExecutable] was queued [1] times instead of [2] times with specific arguments on queue [high-priority].');
    });
});

describe('dumping', function () {
    it('outputs filtered jobs', function () {
        Queue::fake();

        PlainQueueableExecutable::onQueue()->execute('input-1');
        PlainQueueableExecutable::onQueue()->execute('input-2');

        $dumpedValues = [];

        VarDumper::setHandler(function ($value) use (&$dumpedValues) {
            $dumpedValues[] = $value;
        });

        PlainQueueableExecutable::assert()->queued()->dump();

        expect($dumpedValues)->toHaveCount(1)
            ->and($dumpedValues[0])->toHaveCount(2)
            ->and($dumpedValues[0][0]['executable'])->toBe(PlainQueueableExecutable::class)
            ->and($dumpedValues[0][0]['arguments'])->toBe(['input' => 'input-1'])
            ->and($dumpedValues[0][1]['arguments'])->toBe(['input' => 'input-2']);
    });

    it('respects filters', function () {
        Queue::fake();

        PlainQueueableExecutable::onQueue()->execute('input-1');
        PlainQueueableExecutable::onQueue()->execute('input-2');

        $dumpedValues = [];

        VarDumper::setHandler(function ($value) use (&$dumpedValues) {
            $dumpedValues[] = $value;
        });

        PlainQueueableExecutable::assert()->queued()
            ->where(fn (PushedJob $job) => $job->executedWith('input-1'))
            ->dump();

        expect($dumpedValues)->toHaveCount(1)
            ->and($dumpedValues[0])->toHaveCount(1)
            ->and($dumpedValues[0][0]['arguments'])->toBe(['input' => 'input-1']);
    });
});
