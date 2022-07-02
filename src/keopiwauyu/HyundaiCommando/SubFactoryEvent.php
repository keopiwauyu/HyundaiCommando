<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\plugin\Plugin;

/**
 * @extends FactoryEvent<Sub, BaseSubCommand>
 */
class SubFactoryEvent extends FactoryEvent
{
        /**
     * @param array<string, Arg> $args
     */
    public function __construct( $wanter, array $args) {
        $this->setFactory($this->getPlugin(), self::builtInSub($wanter, $args));
        parent::__construct($wanter, $args);
    }

    public function getWanter() : Sub {
        return parent::getWanter();
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