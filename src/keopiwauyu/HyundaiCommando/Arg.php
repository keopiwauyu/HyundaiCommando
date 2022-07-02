<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\exception\ArgumentOrderException;
use CortexPE\Commando\traits\IArgumentable;
use SOFe\AwaitGenerator\Await;
use SOFe\AwaitGenerator\Loading;
use SOFe\AwaitGenerator\Mutex;
use keopiwauyu\HyundaiCommando\ArgConfig;
use libMarshal\MarshalTrait;
use libMarshal\attributes\Field;
use libMarshal\exception\GeneralMarshalException;
use libMarshal\exception\UnmarshalException;

class Arg
{
    use MarshalTrait;

    public string $id;

    /**
     * @var Loading<BaseArgument>
     */
    private Loading $loading;

    public bool $used = false;

    /**
     * @param mixed[] $other
     */
    public function __construct(
        #[Field] public string $name = "", // TODO: support langusges??
        #[Field] public string $type = "",
        #[Field] public bool $optional = false,
        #[Field] public array $other = []
    ) {
    }

    /**
     * @param mixed[] $data
     * @param array<string, self> $args
     * @throws \Exception
     * @throws GeneralMarshalException
     * @throws UnmarshalException
     */
    public static function unmarshalAndLoad(array $data, array &$args, Mutex $lock) : self {
        $self = self::unmarshal($data);
        $self->loading = new Loading(function () use (&$args, $lock, $self) : \Generator {
        yield from $lock->acquire();
        $lock->release();

        $event = new ArgFactoryEvent($self, $args);
        $loaded = yield from $event->getFactory();

        return $loaded;
        });

        return $self;
    }

    /**
     * @var string[]
     */
    private array $depends;

    /**
     * @param string[] $depends
     * @param array<string, self> $args
     * @return \Generator<mixed, mixed, mixed, array<string, BaseArgument>>
     */
    public function setDepends(array $depends, array $args) : \Generator {
        if (isset($this->depends)) throw new \RuntimeException("Arg depends cannot change after set");
        $this->depends = $depends;
$this->checkDependsRecursive($depends, $args, []);

        return yield from Await::all(array_map(
            fn(string $depend) : \Generator => yield from $args[$depend]->loading->get(),
            $depends
        ));
    }

    /**
     * @param string[] $depends
     * @param array<string, self> $args
     * @param string[] $trace
     * @throws \Exception
     */
    private function checkDependsRecursive(array $depends, array $args, array $trace) : void {
        foreach ($depends as $depend) {
            $clone = $trace;
            $clone[] = $depend;
            if (in_array($depend, $trace, true)) throw new \Exception("Recursive arg depend ('" . implode("' => '", $clone) . "')");
            $this->checkDependsRecursive($args[$depend]->depends, $args, $clone);
        }
    }

    public function getType() : string {
        return strtolower($this->type);
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
                        $arg = self::unmarshalAndLoad($id, $args, new Mutex());
                    } catch (GeneralMarshalException|UnmarshalException $err) {
                        throw new \Exception("anonymous arg", -1, $err);
                    }
                }
                $arg->used = true;
                try {
$cmd->registerArgument($position, yield from $arg->loading->get());
                } catch (ArgumentOrderException $err) {
                    throw new \Exception("Bad arg order", -1, $err);
                }
            } 
            }

            return $cmd;
    }
}