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
use pocketmine\lang\Translatable;
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
     * @param array<int, BaseArgument|BaseSubCommand> $args
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
        /**
         * @var BaseArgument[] $args
         */
        foreach ($args as $i => $arg) {
            $this->registerArgument($i++, $arg);
        }

        $d = $this->cmd->getDescription();
        parent::__construct(MainClass::getInstance(), $this->cmd->getName(), "", $this->cmd->getAliases());
        $this->setDescription($d);
        if ($this->cmd instanceof Command) {
            $map->unregister($this->cmd);
        }
        $map->register($this->getFallbackPrefix(), $this);
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
                $args[] = self::$argTypes[$type]($type, true, []); // TODO: found bug !!! break my ph untt test
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

    private function getFallbackPrefix() : string
    {
        $label = $this->cmd instanceof Command ? $this->cmd->getLabel() : $this->cmd->getParent()->getLabel();
        return explode(":", $label)[0];
    }

    public function simpleRegister() : void
    {
        $this->register(Server::getInstance()->getCommandMap());
    }

    public function logRegister() : void
    {
        $this->simpleRegister();
        MainClass::getInstance()->getLogger()->debug("Registered '" . $this->getLabel() . "'");
    }

    /**
     * Resets on plugin enable.
     * @see MainClass::onEnable()
     * @var array<string, callable(string $name, bool $optional, mixed[] $other) : (BaseArgument|BaseSubCommand)>
     */
    public static array $argTypes;

    public static function resetArgTypes() : void
    {
        $types = [
            "Boolean" => [BuiltInArgs::class, "booleanArg"],
            "Integer" => [BuiltInArgs::class, "integerArg"],
            "Float" => [BuiltInArgs::class, "floatArg"],
            "RawString" => [BuiltInArgs::class, "rawStringArg"],
            "Text" => [BuiltInArgs::class, "textArg"],
            "Vector3" => [BuiltInArgs::class, "vector3Arg"],
            "BlockPosition" => [BuiltInArgs::class, "blockPositionArg"],
            "StringEnum" => [BuiltInArgs::class, "stringEnumArg"],
            "SubCommand" => [BuiltInArgs::class, "subCommand"]
        ];
        /**
         * @var array<string, callable(string $name, bool $optional, mixed[] $other) : (BaseArgument|BaseSubCommand)> $types
         */
        self::$argTypes = $types;
    }

    /**
     * @throwss RegistrationException
     */
    public static function configToArg(ArgConfig $config) : BaseArgument|BaseSubCommand
    {
        $type = $config->type;
        $name = $config->name;
        $factory = self::$argTypes[$type] ?? throw new RegistrationException("Arg '$name' has unknown type: $type");
        $name = $config->name;
        return $factory($name, $config->optional, $config->other);
    }

    /**
     * Get command from its label and make it hyundai.
     * Wait until it gets registered if it is not.
     * @param array<int, BaseArgument|BaseSubCommand> $args
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
        return new self($cmd, $args);
    }
}
