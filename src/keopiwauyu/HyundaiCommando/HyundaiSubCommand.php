<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use RuntimeException;
use function array_unshift;

class HyundaiSubCommand extends BaseSubCommand
{
    public SubCommandConfig $config;
    /**
     * @var HyundaiCommand[]
     */
    public array $links = [];
    private HyundaiCommand $hyundaiParent;

    public function getParent() : HyundaiCommand
    {
        return $this->hyundaiParent;
    }

    public function setParent(Command $hyundaiParent) : void
    {
        if (!$hyundaiParent instanceof HyundaiCommand) {
            throw new RuntimeException("HyundaiSubCommand must have a HyundaiCommand parent");
        }
        $this->hyundaiParent = $hyundaiParent;

        foreach ($this->links as $link)$link->logRegister($this->getParent()->getFallbackPrefix());

        parent::setParent($hyundaiParent);
    }

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
        $this->getParent()->onRun($sender, $aliasUsed, $args);
    }
}
