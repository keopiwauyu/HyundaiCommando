<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use CortexPE\Commando\traits\IArgumentable;
use SOFe\AwaitGenerator\Loading;
use libMarshal\MarshalTrait;
use libMarshal\attributes\Field;
use libMarshal\exception\GeneralMarshalException;
use libMarshal\exception\UnmarshalException;

class Sub
{
    use MarshalTrait;

    /**
     * @param string[] $aliases
     * @param array<string|mixed[]>[] $args
     */
    public function __construct(
        #[Field] public string $name, 
        #[Field] public string $description, // TODO: support langusges??
        #[Field] public array $aliases,
        #[Field] public array $args,
        #[Field] public string $permission,
        #[Field] public bool $link
    ) {
    }

    public self $config;

    public bool $used = false;

       /**
     * @var Loading<BaseSubCommand>
     */
    public Loading $loading;

    /**
     * @param mixed[] $data
     * @param array<string, Arg> $args
     * @throws \Exception
     * @throws GeneralMarshalException
     * @throws UnmarshalException
     */
    public static function unmarshalAndLoad(array $data, array $args) : self {
        $self = self::unmarshal($data);
        $self->config = $self;
        $self->loading = new Loading(function () use ($args, $self) : \Generator { // @phpstan-ignore-line fake
        /**
         * @var WantFactoryEvent<self, BaseSubCommand> $event
         */
        $event = new WantFactoryEvent($self, $args);
        $event->call();
        return yield from $event->getFactory();
        });

        return $self;
    }

    /**
     * @template T of IArgumentable
     * @param T $cmd
     * @param array<string|array<string|mixed[]>> $groups
     * @param array<string, Arg> $args
     * @return \Generator<mixed, mixed, mixed, T>
     * @throws \Exception
     */
    public static function registerArgs(IArgumentable $cmd, array $groups, array $args) : \Generator {
            foreach ($groups as $position => $group) {
                if (!is_array($group)) {
                    throw new \Exception("Using subcommand in subcommand");
                }

               foreach ($group as $id) {
                if (!is_array($id)) {
                    $arg = $args[$id] ?? throw new \Exception("Unknown global arg '$id'") ;
                } else {
                    try {
                        $arg = Arg::unmarshal($id);
                    } catch (GeneralMarshalException|UnmarshalException $err) {
                        throw new \Exception("anonymous arg", -1, $err);
                    }
                }
                $arg->used = true;
                try {
$cmd->registerArgument($position, yield from $arg->config->loading->get());
                } catch (ArgumentOrderException $err) {
                    throw new \Exception("Bad argument order", -1, $err);
                }
            } 
            }

            return $cmd;
    }

    /**
     * @param array<string|array<string|mixed[]>> $groups
     * @param array<string, Arg> $args
     * @param array<string, Sub> $subs
     * @return \Generator<mixed, mixed, mixed, BaseCommand>
     * @throws \Exception
     */
    public static function registerArgsAndSubs(BaseCommand $cmd, array $groups, array $args, array $subs) : \Generator {
            foreach ($groups as $position => $group) {
                if (!is_array($group)) {
                    $sub = $subs[$group] ?? throw new \Exception("Unknown global subcommand '$group'");
                } elseif (array_values($group) !== $group) {
                    try {
                        $sub = Sub::unmarshal($group);
                    } catch (GeneralMarshalException|UnmarshalException $err) {
                        throw new \Exception("anonymous subcommand", -1, $err);
                    }
                }
                if (isset($sub)) {
                    $cmd->registerSubCommand(yield from $sub->config->loading->get());
                    unset($groups[$position]);
                    continue;
                }
            }

            return yield from self::registerArgs($cmd, $groups, $args);
    }
}