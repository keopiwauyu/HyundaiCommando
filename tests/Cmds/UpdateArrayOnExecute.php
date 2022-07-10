<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando\Cmds;

use CortexPE\Commando\IRunnable;
use keopiwauyu\HyundaiCommando\ArgConfig;
use keopiwauyu\HyundaiCommando\ArgConfigTest;
use keopiwauyu\HyundaiCommando\Cmds\FakePlugin;
use keopiwauyu\HyundaiCommando\HyundaiCommand;
use keopiwauyu\HyundaiCommando\HyundaiSubCommand;
use keopiwauyu\HyundaiCommando\SubCommandConfig;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;

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

	public static function makeHyundai(array &$update, bool $registerArgs, bool $subCommand) : HyundaiCommand {
        if ($registerArgs) {
            HyundaiCommand::resetArgTypes();
            $args = [];
            foreach (ArgConfigTest::ARG_FACTORY_TO_CLASS as $type => $class) {
                $args[] = HyundaiCommand::$argTypes[$type](new ArgConfig(
                    type: $type,
                    optional: true,
                    name: $class,
                    depends: [],
                    other: []
                )); // TODO: found bug !!! break my ph untt test
            }
        }

        HyundaiCommand::$testPlugin = FakePlugin::get();
		$cmd = new HyundaiCommand(self::make($update), $args ?? [], "fbp:aaa");
        if ($subCommand) {
            PermissionManager::getInstance()->addPermission(new Permission("ddd"));
            $sub = new HyundaiSubCommand("bbb", new SubCommandConfig(
                description: "ccc",
                permission: "ddd",
                aliases: ["eee"],
                args: [],
                links: ["fff"]
            ));
            $cmd->registerSubCommand($sub);
        }

        return $cmd;

	}

	public function execute(CommandSender $sender, string $aliasUsed, array $args) : void {
		($this->updater)($args);
	}
}