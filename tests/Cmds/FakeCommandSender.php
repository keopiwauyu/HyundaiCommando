<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando\Cmds;

use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\lang\Language;
use pocketmine\lang\Translatable;
use pocketmine\permission\PermissionAttachment;
use pocketmine\plugin\Plugin;
use pocketmine\utils\ObjectSet;

class FakeCommandSender implements CommandSender {
		public function getLanguage() : Language {
			var_dump("Get language from fake command sender");
		}

	public function sendMessage(Translatable|string $message) : void {
		throw new \RuntimeException("Send message to command sender: $message");
	}

	public function getServer() : Server {
		throw new \RuntimeException("Get server from fake command sender");
	}

	public function getName() : string {
		throw new \RuntimeException("Get name from fake command sender");
	}

	public function getScreenLineHeight() : int {
		throw new \RuntimeException("Get screen line height from fake command sender");
	}

	public function setScreenLineHeight(?int $height) : void {
		throw new \RuntimeException("Set screen line height of fake command sender");
	}

	public function setBasePermission($name, bool $grant) : void {
		throw new \RuntimeException("Set base permission of fake command sender");
	}

	public function unsetBasePermission($name) : void {
		throw new \RuntimeException("Unset base permission of fake command sender");
	}

	public function isPermissionSet($name) : bool {
		var_dump("Is permission set for fake command sender", $name);
		return true;
	}

	public function hasPermission($name) : bool {
		var_dump("Does fake command sender has permission", $name);
		return true;
	}

	public function addAttachment(Plugin $plugin, ?string $name = null, ?bool $value = null) : PermissionAttachment {
		throw new \RuntimeException(var_export(["Add attachment to fake command sender", $plugin, $name, $value]));
	}

	public function removeAttachment(PermissionAttachment $attachment) : void {
		throw new \RuntimeException(var_export(["Remove attachment from fake command sender", $attachment]));
	}

	public function recalculatePermissions() : array {
		throw new \RuntimeException("Recalculate permissions for fake command sender");
	}

	public function getPermissionRecalculationCallbacks() : ObjectSet {
		throw new \RuntimeException("Get permission recalculation callbacks from fake command sender");
	}

	public function getEffectivePermissions() : array {
		throw new \RuntimeException("Get effective permissions from fake command sender");
	}


}