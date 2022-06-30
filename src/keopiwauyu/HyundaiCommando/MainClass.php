<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\PacketHooker;
use Generator;
use SOFe\AwaitGenerator\Await;
use SOFe\AwaitStd\AwaitStd;
use function array_diff;
use function array_values;
use function is_array;
use function ksort;
use function mkdir;
use function scandir;
use function str_replace;
use function trim;
use function yaml_parse_file;
use libMarshal\exception\GeneralMarshalException;
use libMarshal\exception\UnmarshalException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;

class MainClass extends PluginBase
{
    private static self $instance;

    public static function getInstance() : self
    {
        return self::$instance;
    }

    public function onLoad() : void
    {
        self::$instance = $this;
        $this->getLogger()->info(TextFormat::WHITE . "thax u using HYUNDAI COMMANDO V0.0.1 BY ☕️🥛!");
    }

    private function yamlErr(string $path) : void {
        $this->suicide("yaml_parse_file($path) result is not array");
        
    }

    private static function noTraceErr(\Throwable $err) : string {
        return implode("\n", Utils::printableExceptionInfo($err, []))
    }

    private function globalErr(string $path, string $id, \Throwable $err) : void {
        $this->suicide("Error when parsing $argsPath, '$id': " . self::noTraceErr($err)) ;
    }

    /**
     * @param mixed[] $array
     */
    private function loadedDebug(array $array, string $subject) : void {
        $this->getLogger()->debug("Loaded " . count($array) . " $subject");
    }

    public function onEnable() : void
    {
        Await::g2c($this->awaitEnable());
    }

    /**
     * @return \Generator<mixed, mixed, mixed, void>
     */
    private function awaitEnable() : \Generator {
        $this->getLogger()->info(TextFormat::DARK_GREEN . "I've been enabled!");
        $this->std = AwaitStd::init($this);
        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }

        $lock = new Mutex();
        yield from $mutex->acquire();
        $argsData = @yaml_parse_file($argsPath = $this->getDataFolder() . "args.yml");
        if (!is_array($argsData)) {
            $this->yamlErr($argsPath);
            return;
        }

        $args = [];
        foreach ($argsData as $id => $argData) {
            try {
$config = Arg::unmarshal($argData);
            } catch (GeneralMarshalException|UnmarshalException $err) {
                $this->globalErr($argsPath, $id, $err);
                return;
            }
            try {
$args[$id] = yield from $config->load($args, $lock);
            } catch (\Exception $err) {
                $this->globalErr($argsPath, $id, $err);
                return;
            }
        }
        $this->loadedDebug($args, "global args");

        $subsData = @yaml_parse_file($subsPath = $this->getDataFolder() . "subcommands.yml");
        if (!is_array($subsData)) {
            $this->yamlErr($subsPath);
            return
        }
        $subs = [];
        foreach ($subsData as $id => $subData) {
            try {
$config = Sub::unmarshal($subData);
            } catch (GeneralMarshalException|UnmarshalException $err) {
                $this->globalErr($subsPath, $id, $err);
                return;
            }
            try {
$subs[$id] = yield from $config->load($args);
            } catch (\Exception $err) {
                $this->globalErr($subsPath, $id, $err);
                return;
            }
        }
        $this->loadedDebug($subs, "global subcommands");

        @mkdir($path = $this->getDataFolder() . "cmds/");
        $files = scandir($path);
        $cmds = [];
        foreach (array_diff($files !== false ? $files : [], [".", ".."]) as $file) {
            $prefix = rtrim($file, ".yml");
            $args = [];
            $data = @yaml_parse_file($path . $file);
            if (!is_array($data)) {
                $this->yamlErr($path . $file);
                return;
            }

            foreach ($data as $id => $datum) {
$label = "$prefix:$id";
            $cmd = new HyundaiCommand();
            $cmds[$label] = $cmd;
            try {
Sub::registerArgs($cmd, $datum);
            } catch (\Exception $err) {
                $this->globalErr($path . $file, $id, $err);
                return;
            }
            
            $this->getLogger()->debug("Queued registration for cmd '$label'");
        }

        $map = $this->getServer()->getCommandMap();
        foreach ($cmds as $label => $cmd) {
            Await::f2c(function() use ($label) : \Generator {
                while (($old = $map->getCommand($label)) === null) {
                   yield from $this->awaitEvent(
                    PlayerPreLoginEvent::class,
                    static fn() => true,
                    false,
                    EventPriority::MONITOR,
                    false
                   );
                }

                $map->unregister($old);
                $map->register($cmd);
                $this->getLogger()->debug("Registered cmd '$label'");
            });
        }
    }

    private function suicide(string $description) : void
    {
        $this->getLogger()->critical($description);
        $this->getServer()->getPluginManager()->disablePlugin($this);
    }

    public function onDisable() : void
    {
        $this->getLogger()->info(TextFormat::DARK_RED . "I've been disabled!");
        unset($this->std);
    }

    public AwaitStd $std;

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
    {
        $sender->sendMessage("COMMING SOON!!!!");
        return true;
    }
}
