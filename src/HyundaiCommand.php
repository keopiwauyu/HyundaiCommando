<?php

use CortexPE\Commando\BaseCommand;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class HyundaiCommand extends BaseCommand {

	private Command $cmd;

	/**
	 * @param ArgConfig[] $args
	 */
	public function __construct(Command $cmd, array $args) {
		$map = Server::getInstance()->getCommandMap();
		$perm = $this->cmd->getPermission();
		if ($perm !== null) $this->setPermission($perm);

		foreach ($args as $i => $arg) {
			$type = $arg->type;
			$factory = $this->argTypes[$type] ?? null;
			if ($factory === null) throw new RegistrationException("Unknown arg type: $type");
			$name = $arg->name;
			$cmd->registerArgument($i++, $factory($name, $arg->optional, $arg->other));
		}

		parent::__construct($cmd->getName(), $this->cmd->getDescription(), $this->cmd->getAliases());
		$map->unregister($this->cmd);
		$map->register($this->getFallbackPrefix(), $this);
	}

	protected function prepare(): void {
	}
	
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$this->cmd instanceof IRunnable) $args = array_values($args);
		$this->cmd->execute($sender, $this->cmd, $aliasUsed, $args);
	}

	private function getFallbackPrefix() : string {
		$label = $this->cmd->getLabel();
		return explode(":", $label)[0];
	}

	public function simpleRegister() : void {
		$this->register(Server::getInstance()->getCommandMap());
	}
}
