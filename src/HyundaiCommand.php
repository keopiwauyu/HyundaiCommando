<?php

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;

class HyundaiCommand extends BaseCommand {

	private Command $cmd;

	public function __construct(string $name) {
		$map = Server::getInstance()->getCommandMap();
		$this->cmd = $map->get($this->getName());
		$map->unregister($this->cmd);

		parent::__construct($name, $this->cmd->getDescription(), $this->cmd->getAliases());
		$map->register($this->getFallbackPrefix(), $this);
	}

	protected function prepare(): void {
		$perm = $this->cmd->getPermission();
		if ($perm !== null) $this->setPermission($perm);
	}
	
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$this->cmd instanceof IRunnable) $args = array_values($args);
		$this->cmd->execute($sender, $this->cmd, $aliasUsed, $args);
	}

	private function getFallbackPrefix() : string {
		$label = $this->cmd->getLabel();
		return explode(":", $label)[0];
	}
}
