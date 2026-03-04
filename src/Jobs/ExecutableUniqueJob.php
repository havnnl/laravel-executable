<?php

declare(strict_types=1);

namespace Havn\Executable\Jobs;

use Havn\Executable\Config\QueueableConfig;
use Havn\Executable\Support\ExecutableArguments;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldBeUnique;

/**
 * @internal
 */
class ExecutableUniqueJob extends ExecutableJob implements ShouldBeUnique
{
    public string $uniqueId;

    public ?int $uniqueFor = null;

    public function __construct(object $executable, ExecutableArguments $arguments, QueueableConfig $config)
    {
        parent::__construct($executable, $arguments, $config);

        $this->uniqueId = $config->uniqueId !== null
            ? get_class($executable).':'.$config->uniqueId
            : get_class($executable);

        $this->uniqueFor = $config->uniqueFor;
    }

    public function uniqueVia(): Repository
    {
        return method_exists($this->executable(), 'uniqueVia')
            ? $this->arguments->callOn($this->executable(), 'uniqueVia')
            : resolve(Repository::class);
    }
}
