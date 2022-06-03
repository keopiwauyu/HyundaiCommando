<?php

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;

class HyundaiCommand extends BaseCommand {
	protected function prepare(): void {
		// This is where we'll register our arguments and subcommands
	}
	
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		// This is where the processing will occur if it's NOT handled by other subcommands
	}
}
