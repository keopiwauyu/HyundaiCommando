<?php

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\IRunnable;
use CortexPE\Commando\args\BaseArgument;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class HyundaiCommand extends BaseCommand {

	/**
	 * @param array<int, BaseArgument|BaseSubCommand> $args
	 */
	public function __construct(private Command $this->cmd, array $args) {
		$map = Server::getInstance()->getCommandMap();
		$perm = $this->cmd->getPermission();
		if ($perm !== null) $this->setPermission($perm);

		foreach ($args as $arg) {
			if ($arg instanceof BaseSubCommand) $this->registerSubCommand($arg);
			else $this->registerArgument($arg);
		}

		parent::__construct(MainClass::getInstance(), $this->cmd->getName(), $this->cmd->getDescription(), $this->cmd->getAliases());
		$map->unregister($this->cmd);
		$map->register($this->getFallbackPrefix(), $this);
	}

	protected function prepare(): void {
	}
	
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$this->cmd->execute($sender, $aliasUsed, array_values($args));
	}

	private function getFallbackPrefix() : string {
		$label = $this->cmd->getLabel();
		return explode(":", $label)[0];
	}

	public function simpleRegister() : void {
		$this->register(Server::getInstance()->getCommandMap());
	}

	/**
	 * Resets on plugin enable.
	 * @see MainClass::onEnable()
	 * @var array<string, callable(string $name, bool $optional, mixed[] $other) : BaseArgument|BaseSubCommand>
	 */
	public static array $argTypes;

	/**
	 * @throwss RegistrationException
	 */
	public static function configToArg(ArgConfig $config) : array {
			$type = $config->type;
			$factory = self::$argTypes[$type] ?? throw new RegistrationException("Unknown arg type: $type");
			$name = $config->name;
			 return $factory($name, $config->optional, $config->other);
	}

	/**
	 * Get command from its label and make it hyundai.
	 * Wait until it gets registered if it is not.
	 * @param array<int, BaseArgument|BaseSubCommand> $args
	 * @return \Generator<mixed, mixed, mixed, HyundaiCommand>
	 */
	public static function fromLabel(string $label, array $args) : \Generator {
		$map = $this->getServer()->getCommandMap();
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
		return new self($cmd, $args);

		yield from [];
	}
}
