<?php

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;

class HyundaiCommand extends BaseCommand {

private Command $cmd;

	protected function prepare(): void {
$mao=Server::getInstance()->getCommandMap();
$this->cmd=$mao->get($this->getName());
$mao->unregister($this->cmd);

	}
	
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {

$this->cmd->execute($sender, $this->cmd, aliasUsed, $args);
	}
}
