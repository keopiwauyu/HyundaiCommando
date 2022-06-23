<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\PacketHooker;
use Generator;
use libMarshal\exception\GeneralMarshalException;
use libMarshal\exception\UnmarshalException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
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

    public function onEnable() : void
    {
        HyundaiCommand::resetArgTypes();
        $this->getLogger()->info(TextFormat::DARK_GREEN . "I've been enabled!");
        $this->std = AwaitStd::init($this);

        @mkdir($path = $this->getDataFolder() . "cmds/");
        $files = scandir($path);
        $generators = [];
        foreach (array_diff($files !== false ? $files : [], [".", ".."]) as $file) {
            $label = str_replace(" ", ":", trim($file, ".yml"));
            $args = [];

            $errTemplate = "Error when parsing $path: ";
            $data = yaml_parse_file($path . $file);
            if (!is_array($data)) {
                $this->suicide("yaml_parse_file($path" . "$file) result is not array");
                return;
            }
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
                    $this->suicide("Error when parsing argument $k in command $label: " . $err->getMessage());
                    return;
                }

                $args[$k] = $arg;
            }
            $this->getLogger()->debug("Queued command registration for '$label'");
            $generators[] = (function() use ($label, $args) : Generator {
                $cmd = yield from HyundaiCommand::fromLabel($label, $args);
                $cmd->simpleRegister();
                $this->getLogger()->debug("Registered '$label'");
            })();
        }
        foreach ($generators as $generator) {
            Await::g2c($generator); // @phpstan-ignore-line
        }

        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
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
