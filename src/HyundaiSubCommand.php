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
	
	/**
	 * @param mixed[] $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		array_unshift($args, $this->getName());
		/**
		 * @var string[] $args phpstan levle 9 sooooooooo bad i us elelev 8 in my nextp lugin
		 */
		$this->parent->onRun($sender, $aliasUsed, $args);
	}
}
