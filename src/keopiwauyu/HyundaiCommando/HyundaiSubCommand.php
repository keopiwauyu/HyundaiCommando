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
        $parented = isset($this->parent);
        parent::setParent($parent);
        if ($parented) return;

        $links = $this->config->links;
                if ($links !== [] && !$parent instanceof HyundaiCommand) {
            throw new RegistrationException("Cannot use 'links' when subcommand is registered on cmd '" . $parent->getName() . "' which is a " . get_debug_type($parent));
        }
            assert($parent instanceof HyundaiCommand);

                    $argsss = $parent->getArgumentList();
            $args = [];
            foreach ($argsss as $argss) {
                foreach ($argss as $arg) {
                    $args[] = $arg; // Commando very weird??? hmm
                }
            }
            foreach ($args as $i => $arg) $this->registerArgument($i, $arg);

        foreach ($links as $i => $link) {
            if (!is_string($link)) {
            throw new RegistrationException("Subcommand link $i is not a string'");
            }
            $cmd = new HyundaiCommand($this, $args, $parent->getPrefixedName($link));
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