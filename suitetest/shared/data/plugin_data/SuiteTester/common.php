<?php

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

    public function __construct() {
        $this->std = Main::$std;
        $this->plugin = Main::getInstance();
        $this->server = Server::getInstance();
    }

    public function awaitMessage(Player $who, string $messageSubstring, ...$args) : Generator {
        $expect = strtolower(sprintf($messageSubstring, ...$args));
        $this->server->getLogger()->debug("Waiting for message to {$who->getName()} " . json_encode($expect));
        return yield from $this->std->awaitEvent(
            event: PlayerReceiveMessageEvent::class,
            eventFilter: fn($event) => $event->player === $who && str_contains(strtolower($event->message), $expect),
            consume: false,
            priority: EventPriority::MONITOR,
            handleCancelled: false,
        );
    }
}

function waitForOnePlayerToJoin(Context $context) : \Generator {
        $onlineCount = 0;
        foreach($context->server->getOnlinePlayers() as $player) {
            if($player->isOnline()) {
                $onlineCount += 1;
            }
        }
        if($onlineCount < 1) {
            yield from $context->std->awaitEvent(PlayerJoinEvent::class, fn($_) => count($context->server->getOnlinePlayers()) === 1, false, EventPriority::MONITOR, false);
        }

        yield from $context->std->sleep(10);
    }

function init_steps(Context $context) : Generator {
    yield "wait for HyundaiCommando to initialize" => function() use($context) {
        yield from $context->std->awaitEvent(PluginEnableEvent::class, fn(PluginEnableEvent $event) : bool => $event->getPlugin() instanceof MainClass, false, EventPriority::MONITOR, false);
    };

    yield "wait for one player to join" => function() use ($context) : \Generator {
        yield from waitForOnePlayerToJoin($context);
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

function late_registration_test(Context $context, string $adminName) : Generator {
    yield "register crasher command" => function() : \Generator {
        false && yield;
        $context->server->getCommandMap()->register("fbp", new class("hello", "world", "/hello", []) extends Command {
            public function execute(CommandSender $sender, string $aliasUsed, array $args) : void {
                throw new \RuntimeException("Late registration failed");
            }
        });
    };
    yield "wait for another one player to join" => function() use ($context) : \Generator {
        false && yield;
        yield from waitForOnePlayerToJoin($context);
    };
    yield "wait for Commando too few args message" => function() use($context, $adminName) : \Generator {
        false && yield;
        $admin = $context->server->getPlayerExact($adminName);
        yield from awaitMessage($admin, "§cInsufficient number of arguments given");
    };
    yield "run command" => function() use($context, $adminName) : \Generator {
        false && yield;
        $admin = $context->server->getPlayerExact($adminName);
        $admin->chat("/fbp:hello");
    };
}