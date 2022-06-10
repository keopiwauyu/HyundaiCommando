<?php

declare(strict_types=1);

namespace HyundaiCommmando;

use SOFe\AwaitGenerator\Await;
use SOFe\AwaitStd\AwaitStd;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class MainClass extends PluginBase{

	public function onLoad() : void{
		$this->getLogger()->info(TextFormat::WHITE . "thax u using HYUNDAI COMMANDO V0.0.1 BY ☕️🥛!");
	}

	public function onEnable() : void{
		$this->getLogger()->info(TextFormat::DARK_GREEN . "I've been enabled!");
		$this->std = AwaitStd::init($this);

		$files = scandir($path = $this->getDataFolder() . "cmds/");
		foreach ($files ?: [] as $file) {
			$label = trim($file, ".yml");
			$args = [];

			foreach (yaml_parse_file($path . $file) as $k => $v) {
				$args[$k] = ArgConfig::unmarshal($v);
			}
			Await::f2c(fn() : \Generator => (yield from $this->fromLabel($name, $args))->simpleRegister() && yield from [])
		}
	}

	/**
	 * Get command from its label and make it hyundai.
	 * Wait until it gets registered if it is not.
	 * Suicide on failure.
	 * @param ArgConfig[] $args
	 * \Generator<mixed, mixed, mixed, HyundaiCommand>
	 */
	public function fromLabel(string $label, array $args) : \Generator {
		$map = $this->getServer()->getCommandMap();
		$map->getCommand();
		while (($cmd = $map->getCommand($label)) === null) {
			// TODO: Timeout
			yield from $this->std->awaitEvent(
				PlayerLoginEvent::class,
				fn() => true,
				false,
				EventPriority::MONITOR,
				false
			);
		}
		return $this->fromCommand($cmd, $args);

		yield from [];
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
		unset($this->std);
	}

	public AwaitStd $std;

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
