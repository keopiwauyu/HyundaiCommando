<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\BaseArgument;
use Generator;
use ReflectionClass;
use function array_filter;
use function array_merge;
use function array_unshift;
use function assert;
use function explode;
use function is_bool;
use function is_scalar;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\math\Vector3;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

class HyundaiCommand extends BaseCommand
{

    private Command|HyundaiSubCommand $cmd;

    public function __construct(HyundaiSubCommand $cmd = null) // @phpstan-ignore-line parent consturctor
    {
        if (!isset($cmd)) return;
            
            $this->cmd = $cmd;
            foreach ($cmd->getArgumentList() as $position => $group) {
                foreach ($group as $arg) $cmd->registerArgument($position, $arg);
            }
    }

    public function init(Command $cmd) : void {
        if (isset($this->cmd)) throw new \RuntimeException("Try to init initialized Hyundai Command");
        $this->cmd = $cmd;
        $plugin = $cmd instanceof PluginOwned ? $cmd->getOwningPlugin() : MainClass::getInstance();
        parent::__construct($plugin, $cmd->getName(), "", $cmd->getAliases());
        $this->setDescription($cmd->getDescription());
        $permission = $cmd->getPermission();
        if ($permission !== null) $this->setPermission($permission);
    }

    protected function prepare() : void
    {
    }

    /**
     * @param mixed[] $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void
    {
        $newArgs = [];
        foreach ($args as $arg) {
            $newArgs = array_merge($newArgs, match ( true) {
                is_bool($arg) => [$arg ? "true" : "false"], // TODO: on / off enum blah bla blah
                $arg instanceof Vector3 => ($arg->getX() === $arg->getFloorX() && $arg->getY() === $arg->getFloorY() && $arg->getZ() === $arg->getFloorZ()) ? [(string)$arg->getFloorX(), (string)$arg->getFloorY(), (string)$arg->getFloorZ()] : [(string)$arg->getX(), (string)$arg->getY(), (string)$arg->getZ()],
                is_scalar($arg) => [(string)$arg],
                default => [$arg]
            });
        }
        if ($this->cmd instanceof Command) {
            $cmd = $this->cmd;
        } else {
            $cmd = $this->cmd->getParent();
            array_unshift($newArgs, $this->cmd->getName());
        }
        /**
         * @var string[] $newArgs
         */
        $cmd->execute($sender, $aliasUsed, $newArgs);
    }

    public function logRegister() : void {
        $label = $this->getLabel();
        $map = Server::getInstance()->getCommandMap();
                   $map->register(explode(":", $label)[0], $this);
                MainClass::getInstance()->getLogger()->debug("Registered cmd '$label'"); 
    }
}
