<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;
use RuntimeException;
use function array_unshift;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class HyundaiSubCommand extends BaseSubCommand
{
    public function __construct(string $name, private SubCommandConfig $config) {
        parent::__construct($name, $this->config->description, $this->config->aliases);
        $this->setPermission($this->config->permission);
    }

    /**
     * @throws RegistrationException
     */
    public function setParent(BaseCommand $parent) : void {
        $linked = isset($this->parent);
        parent::setParent($parent);

        $links = $this->config->links;
        if ($linked || $links === []) return;
        if (!$parent instanceof HyundaiCommand) {
            throw new RegistrationException("Cannot use link when subcommand is registered on cmd '" . $parent->getName() . "' which is a " . get_debug_type($parent));
        }
            $argsss = $this->getArgumentList();
            $args = [];
            foreach ($argsss as $argss) {
                foreach ($argss as $arg) {
                    $args[] = $arg; // Commando very weird??? hmm
                }
            }
        foreach ($links as $link) {
            $cmd = new HyundaiCommand($this, $args, $parent);
            $cmd->logRegister();
        }
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
        $this->parent->onRun($sender, $aliasUsed, $args);
    }
}