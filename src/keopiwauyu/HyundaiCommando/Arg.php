<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\args\BaseArgument;
use SOFe\AwaitGenerator\Loading;
use SOFe\AwaitGenerator\Mutex;
use keopiwauyu\HyundaiCommando\ArgConfig;

class Arg
{
    use MarshalTrait;

    private string $id;

    /**
     * @var Loading<BaseArgument|self[]|null>
     */
    private Loading $loading;

    /**
     * @var self[]
     */
    private array $users;

    public function __construct(
        #[Field] public string $name = "", // TODO: support langusges??
        #[Field] public string $type = "",
        #[Field] public bool $optional = false,
        #[Field] public array $depends = [],
        #[Field] public array $other = []
    ) {
    }

    /**
     * @var array<string, self>
     */
    public array $dependsArg;

    /**
     * @param array<string, self> $args
     * @return \Generator<mixed, mixed, mixed, BaseArgument|null>
     */
    public function load(array $args, Mutex $lock) : \Generator {
        yield from $lock->acquire();
        $lock->release();

        $event = new WantFactoryEvent($this);
        $event->call();
        yield from $event->getFactory();
    }
}