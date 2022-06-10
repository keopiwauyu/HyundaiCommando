<?php

declare(strict_types=1);

namespace HyundaiCommando;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerRespawnEvent;

class ExampleListener implements Listener{

	public function __construct(private MainClass $plugin){ }

	/**
	 * @param PlayerRespawnEvent $event
	 *
	 * @priority HIGH
	 */
	public function onDataPacketSendEvent(DataPacketSendEvent $event) : void{
		// TODO: change ugly stirng enum name
	}
}
