<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\IRunnable;
use CortexPE\Commando\args\BaseArgument;
use Generator;
use ReflectionClass;
use function array_filter;
use function array_merge;
use function array_unshift;
use function array_values;
use function assert;
use function explode;
use function is_bool;
use function is_scalar;
use function ksort;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\math\Vector3;

class HyundaiCommand extends BaseCommand
{
    private string $prefixedName;

    /**
     * @param array<BaseArgument|BaseSubCommand> $args
     */
    public function __construct(private Command|HyundaiSubCommand $cmd, array $args, string|self $prefixedName)
    {
        $this->prefixedName = $prefixedName instanceof self ? $prefixedName->prefixedName : $prefixedName;

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

        parent::__construct(MainClass::getInstance(), explode(":", $this->prefixedName)[1] ?? throw new \RuntimeException("Name '$prefixedName' is not prefixed"), "", $this->cmd->getAliases());
        $this->setDescription($this->cmd->getDescription());
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
            $sub = new HyundaiSubCommand("aaa", new SubCommandConfig(
                description: "bbb",
                permission: "ccc",
                aliases: ["ddd"],
                args: [],
                links: ["eee"]
            ));
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
            try {
            $newArgs = [...$newArgs, ...match (true) {
                is_bool($arg) => [$arg ? "true" : "false"], // TODO: on / off enum blah bla blah
                $arg instanceof Vector3 => ($arg->getX() === $arg->getFloorX() && $arg->getY() === $arg->getFloorY() && $arg->getZ() === $arg->getFloorZ()) ? [(string)$arg->getFloorX(), (string)$arg->getFloorY(), (string)$arg->getFloorZ()] : [(string)$arg->getX(), (string)$arg->getY(), (string)$arg->getZ()],
                is_scalar($arg) || $arg instanceof \Stringable => [(string)$arg],
                default => throw new \RuntimeException()
            }];
        } catch (\RuntimeException) {
            MainClass::getInstance()->getLogger()->error("Commando provided unsupported arg type: " . get_debug_type($arg));
        }
        }
        if ($this->cmd instanceof Command) {
        $this->cmd->execute($sender, $aliasUsed, $newArgs);
        } else {
            $this->cmd->onRun($sender, $aliasUsed, $args);
        }
    }

    public function simpleRegister() : void
    {
        $map = Server::getInstance()->getCommandMap();
        // if ($this->isRegistered($map)) throw new \RuntimeException("HyundaiCommand registered to one command map for more than once");

        $map->register(explode(":", $this->prefixedName)[0], $this);
    }

    public function logRegister() : void
    {
        $this->simpleRegister();
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
    public static function fromPrefixedName(string $prefixedName, array $args) : Generator
    {
        $map = Server::getInstance()->getCommandMap();
        while (($cmd = $map->getCommand($prefixedName)) === null) {
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

        return new self($cmd, $args, $prefixedName);
    }
}
