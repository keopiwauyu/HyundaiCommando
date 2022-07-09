<?php

use SOFe\AwaitGenerator\Channel;
use SOFe\AwaitStd\AwaitStd;
use SOFe\SuiteTester\Await;
use SOFe\SuiteTester\Main;
use keopiwauyu\HyundaiCommando\MainClass;
use muqsit\fakeplayer\network\listener\ClosureFakePlayerPacketListener;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\plugin\PluginEnableEvent;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\ClosureTask;

class PlayerReceiveMessageEvent extends Event {
    public function __construct(
        public Player $player,
        public string $message,
        public int $type,
    ) {}
}

class Context {
    /** @var AwaitStd $std do not type hint directly, because included files are not shaded */
    public $std;
    public Plugin $plugin;
    public Server $server;
    /**
     * @var Channel<PlayerReceiveMessageEvent>
     */
    public Channel $messages;

    public function __construct() {
        $this->std = Main::$std;
        $this->plugin = Main::getInstance();
        $this->server = Server::getInstance();
        $this->messages = new Channel();
    }

    public function expectMessage(Player $who, string $messageSubstring, ...$args) : void {
        Await::f2c(function() use ($who, $messageSubstring, $args) : \Generator {
        $expect = strtolower(sprintf($messageSubstring, ...$args));
        $this->server->getLogger()->debug("Testing for message to {$who->getName()} " . json_encode($expect));
        $event = yield from $this->std->awaitEvent(
            event: PlayerReceiveMessageEvent::class,
            eventFilter: fn($event) => $event->player === $who && str_contains(strtolower($event->message), $expect),
            consume: false,
            priority: EventPriority::MONITOR,
            handleCancelled: false,
        );
        $this->messages->sendWithoutWait($event);
        });
    }

    /**
     * @return \Generator<mixed, mixed, mixed, PlayerReceiveMessageEvent[]>
     */
    public function awaitMessages() : \Generator {
        $m = [];
        for ($i=0; $i<$this->messages->getSendQueueSize(); $i++) $m[] = yield from $this->messages->receive();
        return $m;
    }
}

function init_steps(Context $context) : Generator {
    yield "register server crasher command" => function() use ($context) {
        false && yield;
        $context->server->getCommandMap()->register("fbp", new class("crasher", "Crash server", "/crasher", ["crash"]) extends Command {
            /**
             * @param mixed[] $args
             */
            public function execute(CommandSender $sender, string $aliasUsed, array $args) : void {
                throw new \RuntimeException("Crasher command executed");
            }
        });
    };

    yield "wait for HyundaiCommando to initialize" => function() use($context) {
        yield from $context->std->awaitEvent(PluginEnableEvent::class, fn(PluginEnableEvent $event) : bool => $event->getPlugin() instanceof MainClass, false, EventPriority::MONITOR, false);
    };

    yield "wait for two players to join" => function() use($context) {
        $onlineCount = 0;
        foreach($context->server->getOnlinePlayers() as $player) {
            if($player->isOnline()) {
                $onlineCount += 1;
            }
        }
        if($onlineCount < 2) {
            yield from $context->std->awaitEvent(PlayerJoinEvent::class, fn($_) => count($context->server->getOnlinePlayers()) === 2, false, EventPriority::MONITOR, false);
        }

        yield from $context->std->sleep(10);
    };

    yield "setup chat listeners" => function() use($context) {
        false && yield;
        foreach($context->server->getOnlinePlayers() as $player) {
            $player->getNetworkSession()->registerPacketListener(new ClosureFakePlayerPacketListener(
                function(ClientboundPacket $packet, NetworkSession $session) use($player, $context) : void {
                    if($packet instanceof TextPacket) {
                        $context->server->getLogger()->debug("{$player->getName()} received message: $packet->message");

                        $event = new PlayerReceiveMessageEvent($player, $packet->message, $packet->type);
                        $event->call();
                    }
                }
            ));
        }
    };
}


function crash_protector_test(Context $context, string $adminName) : Generator {
    $value = "false";

    yield "expect error message" => function() use($context, $adminName, $value) {
        false && yield;

        $admin = $context->server->getPlayerExact($adminName);
        $context->expectMessage($admin, "Invalid value '$value' for argument #1");
    };
    yield "execute /crash with value" => function() use($context, $adminName, $value) {
        false && yield;

        $admin = $context->server->getPlayerExact($adminName);
        $admin->chat("/crash $value");
    };
    yield "await messages" => function() use ($context) {
        yield from $context->awaitMessages();
    };
}
