<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\plugin\Plugin;

class SubFactoryEvent extends FactoryEvent
{
    /**
     * @param array<string, Arg> $args
     */
    public function __construct(private Sub $wanter, array $args) {
        parent::__construct($args);
        $this->setFactory($this->getPlugin(), self::builtInSub($this->getWanter(), $this->getArgs()));
    }

    public function getWanter() : Sub {
        return $this->wanter;
    }

    /**
     * @var \Generator<mixed, mixed, mixed, BaseSubCommand>
     */
   private \Generator $factory;

    /**
     * @return \Generator<mixed, mixed, mixed, BaseSubCommand>
     */
    public function getFactory() : \Generator {
        return $this->factory;
    }

    /**
     * @param \Generator<mixed, mixed, mixed, BaseSubCommand> $factory
     * @return self
     */
    public function setFactory(Plugin $factoryPlugin, \Generator $factory) : self {
        $this->factoryPlugin = $factoryPlugin;
        $this->factory = $factory;

        return $this;
    }

    /**
     * @param array<string, Arg> $args
     * @return \Generator<mixed, mixed, mixed, BaseSubCommand>
     */
public static function builtInSub(Sub $sub, array $args) : \Generator {
            $subcmd = new HyundaiSubCommand($sub->name, $sub->description, $sub->aliases);
            $subcmd->setPermission($sub->permission);
            if ($sub->link) {
                $subcmd->linked = new HyundaiCommand($subcmd);
            }

            return yield from Arg::registerArgs($subcmd, $sub->args, $args);
        
}
}