<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseSubCommand;

class Sub
{
    use MarshalTrait;

    /**
     * @param string[] $aliases
     * @param array<array-key, string|mixed[]>[] $args
     */
    public function __construct(
        #[Field] public string $description, // TODO: support langusges??
        #[Field] public array $aliases,
        #[Field] public array $args,
        #[Field] public string $permission,
        #[Field] public bool $link
    ) {
    }

    public self $config;

    /**
     * @param array<string, Arg> $args
     * @return \Generator<mixed, mixed, mixed, BaseSubCommand>
     * @throws \Exception
     */
    public function load(array $args) : \Generator {
        $event = new WantFactoryEvent($this, $args);
        $event->call();
        return yield from $event->getFactory();
    }
}