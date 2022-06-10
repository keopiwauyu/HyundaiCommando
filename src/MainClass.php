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

	/**
	 * Suicide on failure.
	 * @param ArgConfig[] $args
	 */
	public function fromCommand(Command $old, array $args) : HyundaiCommand {
		try {
			return new HyundaiCommmando($old, $args);
		} catch (RegistrationException $err) {
			$name = $config->name;
			$this->getLogger()->warning("Error occurred when trying to make this command to hyundai: $name");
			$this->suicide($err->getMessage());
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
