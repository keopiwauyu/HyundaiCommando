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
        #[Field] public array $other = []
    ) {
    }

    public self $config;

    /**
     * @param array<string, self> $args
     * @return \Generator<mixed, mixed, mixed, BaseArgument|null>
     * @throws \Exception
     */
    public function load(array $args, Mutex $lock) : \Generator {
        $event = new WantFactoryEvent($this, $args);
        $event->call();
        return yield from $event->getFactory();
    }

    public function getType() : string {
        return strtolower($this->type);
    }
}