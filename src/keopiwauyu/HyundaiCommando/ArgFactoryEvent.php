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

/**
 * @extends FactoryEvent<Arg, BaseArgument>
 */
class ArgFactoryEvent extends FactoryEvent
{
    /**
     * @param array<string, Arg> $args
     */
    public function __construct(Arg $wanter, array $args) {
        $this->setFactory($this->getPlugin(), self::builtInArgs($wanter, $args));
        parent::__construct($wanter, $args);
    }

    public function getWanter() : Arg {
        return parent::getWanter();
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