<?php

declare(strict_types=1);

namespace HyundaiCommmando;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class MainClass extends PluginBase{

	public function onLoad() : void{
		$this->getLogger()->info(TextFormat::WHITE . "thax u using HYUNDAI COMMANDO V0.0.1 BY ☕️🥛!");
	}

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents(new ExampleListener($this), $this);
		$this->getScheduler()->scheduleRepeatingTask(new BroadcastTask($this->getServer()), 120);
		$this->getLogger()->info(TextFormat::DARK_GREEN . "I've been enabled!");
	}

	public function registerCommand(CommandConifg $config) : void {
		$cmd = new HyundaiCommmando($config->name);
		$i = 0;
		foreach ($config->args as $name => $arg) {
			$type = $arg->type;
			$factory = $this->argTypes[$type] ?? null;
			if ($factory === null) throw new OtherConfigException("Unknown arg type: $type");
			$cmd->registerArgument($i++, $factory($name, $arg->optional, $arg->other));
		}
	}

	private function suicide(string $description) : void {
		$this->getLogger()->critical($description);
		$this->getServer()->getPluginManager()->disablePlugin($this);
	}

	public function onDisable() : void{
		$this->getLogger()->info(TextFormat::DARK_RED . "I've been disabled!");
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch($command->getName()){
			case "commando":
				$sender->sendMessage("Hello " . $sender->getName() . "!");

				return true;
			default:
				throw new \AssertionError("This line will never be executed");
		}
	}

	/**
	 * @var array<string, callable(string $name, bool $optional, mixed[] $other) : BaseArgument>
	 */
	public array $argTypes = [
		"Boolean" => [BuiltInArgs::class, "booleanArg"],
		"Integer" => [BuiltInArgs::class, "integerArg"],
		"Float" => [BuiltInArgs::class, "floatArg"],
		"RawString" => [BuiltInArgs::class, "rawStringArg"],
		"Text" => [BuiltInArgs::class, "textArg"],
		"Vector3" => [BuiltInArgs::class, "vector3Arg"],
		"BlockPosition" => [BuiltInArgs::class, "blockPositionArg"],
		"StringEnum" => [BuiltInArgs::class, "stringEnumArg"]
	];
}
