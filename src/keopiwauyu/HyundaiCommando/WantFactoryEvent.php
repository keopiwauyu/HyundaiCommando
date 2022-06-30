<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\BaseArgument;
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
     */
    public function __construct(private Arg|Sub $wanter) {
        parent::__construct(MainClass::getInstance());
        $this->factoryPlugin = MainClass::getInstance();
        if ($wanter instanceof Sub) {
            // TODO: build int args
        }
    }

    /**
     * @var \Closure(T) : \Generator<mixed, mixed, mixed, U>
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
        $factory = $this->factory;
        return $factory($this->getWanter());
    }

    /**
     * @param \Closure(T) : \Generator<mixed, mixed, mixed, U> $factory
     */
    public function setFactory(Plugin $factoryPlugin, \Closure $factory) : self {
        $this->factoryPlugin = $factoryPlugin;
        $this->factory = $factory;

        return $this;
    }
}