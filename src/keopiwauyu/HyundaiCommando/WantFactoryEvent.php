<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\BlockPositionArgument;
use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\args\FloatArgument;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\args\Vector3Argument;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\plugin\Plugin;

/**
 * @template T of Arg|Sub
 * @template U of BaseArgument|BaseSubCommand
 */
class WantFactoryEvent extends PluginEvent
{
    /**
     * @param T $wanter
     * @param array<string, Arg> $args
     */
    public function __construct(private Arg|Sub $wanter, private array $args) {
        parent::__construct(MainClass::getInstance());
        $this->factoryPlugin = MainClass::getInstance();
        $this->factory = ($wanter instanceof Sub ? static function (Arg $arg, array $args) : \Generator {
$class = match ($arg->config->getType()) {
    "boolean" => BooleanArgument::class,
    "integer" => IntegerArgument::class,
    "float" => FloatArgument::class,
    "rawstring" => RawStringArgument::class,
    "vector3" => Vector3Argument::class,
    "blockposition" => BlockPositionArgument::class,
    "stringenum" => throw new \Exception("String enum arg not supported now"),
    default => throw new \Exception("No factory for arg");
}();
return new $class($arg->config->name, $arg->config->optional);
        } : static function (Sub $sub, array $args) : \Generator {
            $subcmd = new HyundaiSubCommand($sub->config->name, $sub->config->description, $sub->config->aliases);
            $subcmd->setPermission($sub->config->permission);

            return Sub::registerArgs($subcmd, $sub->config->args, $args);
        })($this->getWanter(), $this->getArgs());
    }

    /**
     * @var \Generator<mixed, mixed, mixed, U>
     */
   private \Closure $factory;

   /**
    * @return T
    */
    public function getWanter() : Arg|Sub {
        return $this->wanter;
    }

    private Plugin $factoryPlugin;

    public function getFactoryPlugin() : Plugin {
        return $this->factoryPlugin;
    }

    /**
     * @return \Generator<mixed, mixed, mixed, U>
     */
    public function getFactory() : \Generator {
        return $this->factory;
    }

    /**
     * @param \Closure(T, array) : \Generator<mixed, mixed, mixed, U> $factory
     */
    public function setFactory(Plugin $factoryPlugin, \Closure $factory) : self {
        $this->factoryPlugin = $factoryPlugin;
        $this->factory = $factory($this->getWanter(), $this->getArgs());

        return $this;
    }

    /**
     * @return array<string, Arg>
     */
    public function getArgs() : array {
        return $this->args;
    }
}