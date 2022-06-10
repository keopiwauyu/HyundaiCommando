<?php

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\IRunnable;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class HyundaiCommand extends BaseCommand {

	/**
	 * @param ArgConfig[] $args
	 */
	public function __construct(private Command $this->cmd, array $args) {
		$map = Server::getInstance()->getCommandMap();
		$perm = $this->cmd->getPermission();
		if ($perm !== null) $this->setPermission($perm);

		foreach ($args as $i => $arg) {
			$type = $arg->type;
			$factory = self::argTypes[$type] ?? null;
			if ($factory === null) throw new RegistrationException("Unknown arg type: $type");
			$name = $arg->name;
			$this->registerArgument($i++, $factory($name, $arg->optional, $arg->other));
		}

		parent::__construct(MainClass::getInstance(), $this->cmd->getName(), $this->cmd->getDescription(), $this->cmd->getAliases());
		$map->unregister($this->cmd);
		$map->register($this->getFallbackPrefix(), $this);
	}

	protected function prepare(): void {
	}
	
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$this->cmd instanceof IRunnable) $args = array_values($args);
		$this->cmd->execute($sender, $aliasUsed, $args);
	}

	private function getFallbackPrefix() : string {
		$label = $this->cmd->getLabel();
		return explode(":", $label)[0];
	}

	public function simpleRegister() : void {
		$this->register(Server::getInstance()->getCommandMap());
	}

	/**
	 * Resets on plugin enable.
	 * @see MainClass::onEnable()
	 * @var array<string, callable(string $name, bool $optional, mixed[] $other) : BaseArgument>
	 */
	public static array $argTypes;
}
