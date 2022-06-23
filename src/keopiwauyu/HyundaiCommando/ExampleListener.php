<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;

class ExampleListener implements Listener
{

    /**
     * @priority HIGH
     */
    public function onDataPacketSendEvent(DataPacketSendEvent $event) : void
    {
        // TODO: change ugly stirng enum name
    }
}
