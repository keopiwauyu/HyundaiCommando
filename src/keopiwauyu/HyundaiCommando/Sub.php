<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\traits\IArgumentable;

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

    private bool $used = false;

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

    /**
     * @template T of IArgumentable
     * @param T $cmd
     * @param array<int, array<array-key<string|mixed[]>> $groups
     * @param array<string, Arg> $args
     * @return T
     * @throws \Exception
     */
    public static function registerArgs(IArgumentable $cmd, array $groups, array $args) : IArgumentable {
            foreach ($groups as $position => $group) foreach ($group as $id) {
                // TODO: anonyous nad link
                $arg = $args[$id] ?? throw new \Exception("Unknown global arg '$id'");
                $arg->used = true;
                try {
$cmd->registerArgument($position, yield from $arg->loading->get());
                } catch (ArgumentOrderException $err) {
                    throw new \Exception("Bad argument order", -1, $err);
                }
            }
    }
}