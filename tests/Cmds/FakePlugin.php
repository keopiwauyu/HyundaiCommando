<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando\Cmds;

use pocketmine\Server;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginDescription;
use pocketmine\plugin\PluginLoader;
use pocketmine\plugin\ResourceProvider;
use pocketmine\scheduler\TaskScheduler;

class FakePlugin implements Plugin {
	public static function get() : self {
		$r = new \ReflectionClass(self::class);
		return $r->newInstanceWithoutConstructor();
	}

	public function __construct(PluginLoader $loader, Server $server, PluginDescription $description, string $dataFolder, string $file, ResourceProvider $resourceProvider) {
		var_dump("New fake plugin", $loader, $server, $description, $dataFolder, $file, $resourceProvider);
	}

	public function isEnabled() : bool
	{
		return true;
	}

	public function onEnableStateChange(bool $enabled) : void
	{
		throw new \RuntimeException("Fake plugin enable state changed to $enabled");
	}

	public function getDataFolder() : string
	{
		throw new \RuntimeException("Get data folder from fake plugin");
	}

	public function getDescription() : PluginDescription
	{
		throw new \RuntimeException("Get description from fake plugin");
	}

	public function getName() : string
	{
		return "FakePlugin";
	}

	public function getLogger() : \AttachableLogger {
		throw new \RuntimeException("Get logger from fake plugin");
	}

	public function getPluginLoader() : PluginLoader
	{
		throw new \RuntimeException("Get plugin loader from fake plugin");
	}

	public function getScheduler() : TaskScheduler
	{
		throw new \RuntimeException("Get scheduler from fake plugin");
	}
}