<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando\Cmds;

use CortexPE\Commando\IRunnable;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;

class UpdateArrayOnExecute extends Command {
	private \Closure $updater;

	public static function make(array &$update) : self {
		$n = new self("aa", "bb", "cc", [
			"dd",
			"ee"
		]);
		$n->updater = function(array $args) use (&$update) : void {
			$update = $args;
		};

		return $n;
	}

	public function execute(CommandSender $sender, string $aliasUsed, array $args) : void {
		($this->updater)($args);
	}
}