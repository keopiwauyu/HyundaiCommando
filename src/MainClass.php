<?php

declare(strict_types=1);

namespace HyundaiCommmando;

use SOFe\AwaitGenerator\Await;
use SOFe\AwaitStd\AwaitStd;
use libMarshal\exception\GeneralMarshalException;
use libMarshal\exception\UnmarshalException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class MainClass extends PluginBase{

	private static self $instance;

	private static function getInstance() : self
	{
		return self::$instance;
	}

	public function onLoad() : void{
		self::$instance = $this;
		$this->getLogger()->info(TextFormat::WHITE . "thax u using HYUNDAI COMMANDO V0.0.1 BY ☕️🥛!");
	}

	public function onEnable() : void{
		HyundaiCommand::resetArgTypes();
		$this->getLogger()->info(TextFormat::DARK_GREEN . "I've been enabled!");
		$this->std = AwaitStd::init($this);

		$files = scandir($path = $this->getDataFolder() . "cmds/");
		$generators = [];
		foreach ($files ?: [] as $file) {
			$label = trim($file, ".yml");
			$args = [];

$errTemplate = "Error when parsing $path: ";
$data = yaml_parse_file($path . $file);
ksort($data);
$data = array_values($data);
			foreach ($data as $k => $v) {
				try {
$config = ArgConfig::unmarshal($v);
				} catch (GeneralMarshalException|UnmarshalException $err) {
					$this->suicide($errTemplate . $err->getMessage());
					return;
				}
				try {
					$arg = HyundaiCommand::configToArg($config);
				} catch (RegistrationException $err) {
					$this->suicide("Error when parsing argument $k in command $label: ". $err->getMessage());
					return;
				}

				$args[$k] = $arg;
			}
				$generators[] = (fn() : \Generator => ( yield from HyundaiCommand::fromLabel($name, $clone))->simpleRegister())();
		}
		foreach ($generators as $generator) {
			Await::g2c($generator);
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
	}
}
