<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\traits\IArgumentable;
use libMarshal\exception\GeneralMarshalException;
use libMarshal\exception\UnmarshalException;

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
     * @return \Generator<mixed, mixed, mixed, T>
     * @throws \Exception
     */
    public static function registerArgs(IArgumentable $cmd, array $groups, array $args) : \Generator {
            foreach ($groups as $position => $group) {
                if (!is_array($args)) {
                    throw new \Exception("Using subcommand in subcommand");
                }

               foreach ($group as $id) {
                if (!is_array($id)) {
                    $arg = $args[$id] ?? throw new \Exception("Unknown global arg '$id'") ;
                } else {
                    try {
                        $arg = Arg::unmarshal($id);
                    } catch (GeneralMarshalException|UnmarshalException $err) {
                        throw new \Exception("anonymous arg '$id'", -1, $err);
                    }
                }
                $arg->used = true;
                try {
$cmd->registerArgument($position, yield from $arg->loading->get());
                } catch (ArgumentOrderException $err) {
                    throw new \Exception("Bad argument order", -1, $err);
                }
            } 
            }
    }

    /**
     * @param array<int, array<array-key<string|mixed[]>> $groups
     * @param array<string, Arg> $args
     * @param array<string, Sub> $subs
     * @return \Generator<mixed, mixed, mixed, BaseCommand>
     * @throws \Exception
     */
    public static function registerArgsAndSubs(BaseCommand $cmd, array $groups, array $args, array $subs) : \Generator {
            foreach ($groups as $position => $group) {
                if (!is_array($args)) {
                    $sub = $subs[$id] ?? throw new \Exception("Unknown global subcommand '$id'");
                } elseif (array_values($id) !== $id) {
                    try {
                        $sub = Sub::unmarshal($id);
                    } catch (GeneralMarshalException|UnmarshalException $err) {
                        throw new \Exception("anonymous subcommand '$id'", -1, $err);
                    }
                }
                if (isset($sub)) {
                    $cmd->registerSubCommand(yield from $sub->loading->get());
                    continue;
                }

               foreach ($group as $id) {
                if (!is_array($id)) {
                    $arg = $args[$id] ?? throw new \Exception("Unknown global arg '$id'") ;
                } else {
                    try {
                        $arg = Arg::unmarshal($id);
                    } catch (GeneralMarshalException|UnmarshalException $err) {
                        throw new \Exception("anonymous arg '$id'", -1, $err);
                    }
                }
                $arg->used = true;
                try {
$cmd->registerArgument($position, yield from $arg->loading->get());
                } catch (ArgumentOrderException $err) {
                    throw new \Exception("Bad argument order", -1, $err);
                }
            } 
            }
    }
}