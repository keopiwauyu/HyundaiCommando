<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\PacketHooker;
use ErrorException;
use Exception;
use Generator;
use libMarshal\exception\GeneralMarshalException;
use libMarshal\exception\UnmarshalException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use SOFe\AwaitGenerator\Await;
use SOFe\AwaitStd\AwaitStd;
use function array_diff;
use function count;
use function file_exists;
use function implode;
use function is_array;
use function mkdir;
use function scandir;
use function str_replace;
use function trim;
use function var_export;
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

    /**
     * @return array<BaseArgument|BaseSubCommand>
     * @throws Exception
     */
    private function loadGlobalArgs() : array
    {
        $path = $this->getDataFolder() . "global-args.yml";
        if (!file_exists($path)) {
            return [];
        }
        $data = @yaml_parse_file($path);
        if (!is_array($data)) {
            throw new Exception("yaml_parse_file($path) result is not array");
        }

        $configs = [];
        foreach ($data as $name => $datum) {
            try {
                $configs[$name] = ArgConfig::unmarshal($datum);
            } catch (GeneralMarshalException|UnmarshalException $err) {
                throw new Exception("'$name': " . $err->getMessage());
            }
        }
        $this->getLogger()->debug("Global arg configs: " . var_export($configs, true));

        $orders = [];
        foreach ($configs as $name => $config) {
            try {
                ArgConfig::arrangeLoadOrder($configs, $orders, $name, []);
            } catch (Exception $err) {
                throw new Exception("'$name': " . $err->getMessage());
            }
        }
        $this->getLogger()->debug("Global args load order: " . var_export($orders, true));

        $args = [];
        foreach ($orders as $name) {
            $config = $configs[$name];
            foreach ($config->depends as $depend) {
                $config->dependeds[$depend] = $args[$depend];
            }
            $args[$name] = HyundaiCommand::configToArg($config);
        }

        return $args;
    }

    public function onEnable() : void
    {
        HyundaiCommand::resetArgTypes();
        $this->getLogger()->info(TextFormat::DARK_GREEN . "I've been enabled!");
        $this->std = AwaitStd::init($this);

        try {
            $globalArgs = $this->loadGlobalArgs();
        } catch (RegistrationException $err) {
            $this->suicide("Error when loading global arg " . $err->getMessage(), $err->getTrace());
            return;
        }
        $this->getLogger()->debug("Loaded " . count($globalArgs) . " global args");


        @mkdir($path = $this->getDataFolder() . "cmds/");
        $files = scandir($path);
        $generators = [];
        foreach (array_diff($files !== false ? $files : [], [".", ".."]) as $file) {
            $prefixedName = str_replace(" ", ":", trim($file, ".yml"));
            $args = [];

            $errTemplate = "Error when parsing $path" . "$file: ";
            $data = yaml_parse_file($path . $file);
            if (!is_array($data)) {
                $this->suicide("yaml_parse_file($path" . "$file) result is not array", Utils::currentTrace());
                return;
            }
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    try {
                        $config = ArgConfig::unmarshal($v);
                    } catch (GeneralMarshalException|UnmarshalException $err) {
                        $this->suicide($errTemplate . $err->getMessage(), $err->getTrace());
                        return;
                    }
                    try {
                        $arg = HyundaiCommand::configToArg($config);
                    } catch (RegistrationException $err) {
                        $this->suicide("Error when parsing arg '$k' in command '$prefixedName': " . $err->getMessage(), $err->getTrace());
                        return;
                    }
                } else {
                    $arg = $globalArgs[$v] ?? null;
                    if ($arg === null) {
                        $this->suicide("Unknown global arg '$v'", Utils::currentTrace());
                        return;
                    }
                }
                $args[$k] = $arg;
            }
            $this->getLogger()->debug("Queued command registration for '$prefixedName'");
            $generators[] = (function() use ($prefixedName, $args) : Generator {
                $cmd = yield from HyundaiCommand::fromPrefixedName($prefixedName, $args);
                $cmd->logRegister();
            })();
        }
        foreach ($generators as $generator) {
            Await::g2c($generator); // @phpstan-ignore-line
        }

        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }
    }

    /**
     * @param mixed[][] $trace
     */
    private function suicide(string $description, array $trace) : void
    {
        $this->getLogger()->critical($description);
        $this->getLogger()->debug(implode("\n", Utils::printableTrace($trace)));
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
