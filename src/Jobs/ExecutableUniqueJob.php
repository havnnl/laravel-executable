<?php

declare(strict_types=1);

namespace Havn\Executable\Jobs;

use Havn\Executable\Config\QueueableConfig;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldBeUnique;

/**
 * @internal
 */
class ExecutableUniqueJob extends ExecutableJob implements ShouldBeUnique
{
    public string $uniqueId;

    public ?int $uniqueFor = null;

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function __construct(object $executable, array $arguments, QueueableConfig $config)
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
            ? $this->invoke($this->executable(), 'uniqueVia', $this->arguments())
            : resolve(Repository::class);
    }
}
