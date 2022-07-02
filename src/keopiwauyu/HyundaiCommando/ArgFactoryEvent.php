<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\BlockPositionArgument;
use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\args\FloatArgument;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\args\Vector3Argument;
use pocketmine\plugin\Plugin;

class ArgFactoryEvent extends FactoryEvent
{
    /**
     * @param array<string, Arg> $args
     */
    public function __construct(private Arg $wanter, array $args) {
        parent::__construct($args);
        $this->setFactory($this->getPlugin(), self::builtInArgs($this->getWanter(), $this->getArgs()));
    }

    public function getWanter() : Arg {
        return $this->wanter;
    }

    /**
     * @var \Generator<mixed, mixed, mixed, BaseArgument>
     */
   private \Generator $factory;

    /**
     * @return \Generator<mixed, mixed, mixed, BaseArgument>
     */
    public function getFactory() : \Generator {
        return $this->factory;
    }

    /**
     * @param \Generator<mixed, mixed, mixed, BaseArgument> $factory
     * @return self
     */
    public function setFactory(Plugin $factoryPlugin, \Generator $factory) : self {
        $this->factoryPlugin = $factoryPlugin;
        $this->factory = $factory;

        return $this;
    }

    /**
     * @param array<string, Arg> $args
     * @return \Generator<mixed, mixed, mixed, BaseArgument>
     */
    public static function builtInArgs(Arg $arg, array $args) : \Generator {
        yield from [];

$class = match ($arg->getType()) {
    "boolean" => BooleanArgument::class,
    "integer" => IntegerArgument::class,
    "float" => FloatArgument::class,
    "rawstring" => RawStringArgument::class,
    "vector3" => Vector3Argument::class,
    "blockposition" => BlockPositionArgument::class,
    "stringenum" => throw new \Exception("String enum arg not supported now"),
    default => throw new \Exception("No factory for arg")
};
return new $class($arg->name, $arg->optional);
    }
}