<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use function array_unshift;

class HyundaiSubCommand extends BaseSubCommand
{
    protected function prepare() : void
    {
    }

    /**
     * @param mixed[] $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void
    {
        array_unshift($args, $this->getName());
        /**
         * @var string[] $args phpstan levle 9 sooooooooo bad i us elelev 8 in my nextp lugin
         */
        $this->parent->onRun($sender, $aliasUsed, $args);
    }
}
