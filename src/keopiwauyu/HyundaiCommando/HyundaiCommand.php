<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;
use Generator;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\math\Vector3;
use pocketmine\Server;
use ReflectionClass;
use function array_filter;
use function array_merge;
use function array_unshift;
use function assert;
use function explode;
use function is_bool;
use function is_scalar;

class HyundaiCommand extends BaseCommand
{

    /**
     * @param array<BaseArgument|BaseSubCommand> $args
     */
    public function __construct(private Command|HyundaiSubCommand $cmd, array $args)
    {
        $map = Server::getInstance()->getCommandMap();
        $perm = $this->cmd->getPermission();
        if ($perm !== null) {
            $this->setPermission($perm);
        }

        $args = array_filter($args, function (BaseArgument|BaseSubCommand $arg) : bool {
            if ($arg instanceof BaseSubCommand) {
                $this->registerSubCommand($arg);
                return false; // delete from arrasy.
            }

            return true;
        });
        ksort($args);
        $args = array_values($args);

        /**
         * @var BaseArgument[] $args
         */
        foreach ($args as $i => $arg) {
            $this->registerArgument($i++, $arg);
        }

        $d = $this->cmd->getDescription();
        parent::__construct(MainClass::getInstance(), $this->cmd->getName(), "", $this->cmd->getAliases());
        $this->setDescription($d);
    }

    /**
     * @internal DO NOT CALL FUNCTION NO TAPI !!!!!!!!!!!!!!!!
     */
    public static function createForTesting(Command $cmd, bool $registerArgs, bool $subCommand) : self
    {
        $r = new ReflectionClass(self::class);
        $n = $r->newInstanceWithoutConstructor();
        $perm = $cmd->getPermission();
        if ($perm !== null) {
            $n->setPermission($perm);
        } // TODO: ithink 100% require permission in pm4????
        $n->cmd = $cmd;

        if ($registerArgs || $subCommand) {
            $args = [];
            foreach (ArgConfigTest::ARG_FACTORY_TO_CLASS as $type => $class) {
                $args[] = self::$argTypes[$type](new ArgConfig(
                    type: $type,
                    optional: true,
                    name: $class,
                    depends: [],
                    other: []
                )); // TODO: found bug !!! break my ph untt test
            }
        }

        if ($registerArgs) {
            /**
             * @var BaseArgument[] $args
             */
            assert(isset($args));
            foreach ($args as $i => $arg) {
                $n->registerArgument($i, $arg);
            }
        }
        if ($subCommand) {
            $sub = new HyundaiSubCommand("aaa", "bbb", [
                "ccc",
                "ddd"
            ]);
            if ($registerArgs) {
                foreach ($args as $i => $arg) {
                    $sub->registerArgument($i, $arg);
                }
            }
            $n->registerSubCommand($sub);
        }

        return $n;
    }

    protected function prepare() : void
    {
    }

    /**
     * @param mixed[] $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void
    {
        $newArgs = [];
        foreach ($args as $arg) {
            $newArgs = array_merge($newArgs, match ( true) {
                is_bool($arg) => [$arg ? "true" : "false"], // TODO: on / off enum blah bla blah
                $arg instanceof Vector3 => ($arg->getX() === $arg->getFloorX() && $arg->getY() === $arg->getFloorY() && $arg->getZ() === $arg->getFloorZ()) ? [(string)$arg->getFloorX(), (string)$arg->getFloorY(), (string)$arg->getFloorZ()] : [(string)$arg->getX(), (string)$arg->getY(), (string)$arg->getZ()],
                is_scalar($arg) => [(string)$arg],
                default => [$arg]
            });
        }
        if ($this->cmd instanceof Command) {
            $cmd = $this->cmd;
        } else {
            $cmd = $this->cmd->getParent();
            array_unshift($newArgs, $this->cmd->getName());
        }
        /**
         * @var string[] $newArgs
         */
        $cmd->execute($sender, $aliasUsed, $newArgs);
    }

    public function getFallbackPrefix() : string
    {
        $label = $this->cmd instanceof Command ? $this->cmd->getLabel() : $this->cmd->getParent()->getLabel();
        return explode(":", $label)[0];
    }

    public function simpleRegister(string $fallbackPrefix) : void
    {
        $name = $this->getName();
        $this->setLabel("$fallbackPrefix:$name");
        $map = Server::getInstance()->getCommandMap();$map->register($fallbackPrefix, $this);
    }

    public function logRegister(string $fallbackPrefix) : void
    {
        $this->simpleRegister($fallbackPrefix);
        MainClass::getInstance()->getLogger()->debug("Registered '" . $this->getLabel() . "'");
    }

    /**
     * @see MainClass::onEnable() Resets on plugin enable.
     * @var array<string, callable(ArgConfig) : (BaseArgument|BaseSubCommand)>
     */
    public static array $argTypes;

    public static function resetArgTypes() : void
    {
        self::$argTypes["Boolean"] = [BuiltInArgs::class, "booleanArg"];
        self::$argTypes["Integer"] = [BuiltInArgs::class, "integerArg"];
        self::$argTypes["Float"] = [BuiltInArgs::class, "floatArg"];
        self::$argTypes["RawString"] = [BuiltInArgs::class, "rawStringArg"];
        self::$argTypes["Text"] = [BuiltInArgs::class, "textArg"];
        self::$argTypes["Vector3"] = [BuiltInArgs::class, "vector3Arg"];
        self::$argTypes["BlockPosition"] = [BuiltInArgs::class, "blockPositionArg"];
        self::$argTypes["StringEnum"] = [BuiltInArgs::class, "stringEnumArg"];
        self::$argTypes["SubCommand"] = [BuiltInArgs::class, "subCommand"];
    }

    /**
     * @throws RegistrationException
     */
    public static function configToArg(ArgConfig $config) : BaseArgument|BaseSubCommand
    {
        $type = $config->type;
        $name = $config->name;
        $factory = self::$argTypes[$type] ?? throw new RegistrationException("Arg '$name' has unknown type: $type");
        $name = $config->name;
        return $factory($config);
    }

    /**
     * Get command from its label and make it hyundai.
     * Wait until it gets registered if it is not.
     * @param array<BaseArgument|BaseSubCommand> $args
     * @return Generator<mixed, mixed, mixed, HyundaiCommand>
     */
    public static function fromLabel(string $label, array $args) : Generator
    {
        $map = Server::getInstance()->getCommandMap();
        while (($cmd = $map->getCommand($label)) === null) {
            // TODO: Timeout
            yield from MainClass::getInstance()->std->awaitEvent(
                PlayerLoginEvent::class,
                fn() => true,
                false,
                EventPriority::MONITOR,
                false
            );
        }
        assert(isset($cmd));
        $map->unregister($cmd);

        return new self($cmd, $args);
    }
}
