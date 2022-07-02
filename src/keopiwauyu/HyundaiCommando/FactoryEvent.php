<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\plugin\Plugin;

abstract class FactoryEvent extends PluginEvent
{
    /**
     * @param array<string, Arg> $args
     */
    public function __construct(private array $args) {
        parent::__construct(MainClass::getInstance());
    }

    /**
     * @return array<string, Arg>
     */
    public function getArgs() : array {
        return $this->args;
    }

    protected Plugin $factoryPlugin;

    public function getFactoryPlugin() : Plugin {
        return $this->factoryPlugin;
    }
}