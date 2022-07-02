<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\BaseArgument;
use PHPUnit\Framework\TestCase;
use SOFe\AwaitGenerator\Await;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\plugin\Plugin;

/**
 * @template T of Arg|Sub
 * @template U of BaseArgument|BaseSubCommand
 */
abstract class FactoryEvent extends PluginEvent
{
    /**
     * @param T $wanter
     * @param array<string, Arg> $args
     */
    public function __construct(private Arg|Sub $wanter, private array $args) {
        parent::__construct(MainClass::getInstance());
               if (isset($this->testMode)) {
                Await::g2c($this->getFactory()); // @phpstan-ignore-line phpstan no
        }
        else $this->call(); 
    }

    /**
     * @internal TEST ONLY !!!!!!!!!!!!
     */
    public static TestCase $testMode;

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

    /**
     * @var \Generator<mixed, mixed, mixed, U>
     */
    private \Generator $factory;

    /**
     * @param \Generator<mixed, mixed, mixed, U> $factory
     * @return static
     */
    public function setFactory(Plugin $plugin, \Generator $factory) : static {
        $this->factoryPlugin = $plugin;
        $this->factory = $factory;

        return $this;
    }

    /**
     * @return \Generator<mixed, mixed, mixed, U>
     */
    public function getFactory() : \Generator {
        return $this->factory;
    }

    /**
     * @return T
     */
    protected function getWanter() : Arg|Sub {
        return $this->wanter;
    }
}