<?php

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\IRunnable;
use CortexPE\Commando\args\BaseArgument;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class HyundaiSubCommand extends BaseSubCommand {

	protected function prepare(): void {
	}
	
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		array_unshift($args, $this->getName());
		$this->parent->execute($sender, $aliasUsed, $args);
	}
}
