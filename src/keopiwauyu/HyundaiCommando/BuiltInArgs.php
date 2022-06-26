<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\BlockPositionArgument;
use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\args\FloatArgument;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\args\Vector3Argument;
use CortexPE\Commando\exception\ArgumentOrderException;
use function array_values;
use function is_scalar;
use function ksort;
use libMarshal\exception\GeneralMarshalException;
use libMarshal\exception\UnmarshalException;

class BuiltInArgs
{

    /**
     * @param mixed[] $other
     */
    public static function booleanArg(string $name, bool $optional, array $other) : BaseArgument
    {
        return new BooleanArgument($name, $optional);
    }

    /**
     * @param mixed[] $other
     */
    public static function integerArg(string $name, bool $optional, array $other) : BaseArgument
    {
        return new IntegerArgument($name, $optional);
    }

    /**
     * @param mixed[] $other
     */
    public static function floatArg(string $name, bool $optional, array $other) : BaseArgument
    {
        return new FloatArgument($name, $optional);
    }

    /**
     * @param mixed[] $other
     */
    public static function rawStringArg(string $name, bool $optional, array $other) : BaseArgument
    {
        return new RawStringArgument($name, $optional);
    }

    /**
     * @param mixed[] $other
     */
    public static function textArg(string $name, bool $optional, array $other) : BaseArgument
    {
        return new TextArgument($name, $optional);
    }

    /**
     * @param mixed[] $other
     */
    public static function vector3Arg(string $name, bool $optional, array $other) : BaseArgument
    {
        return new Vector3Argument($name, $optional);
    }

    /**
     * @param mixed[] $other
     */
    public static function blockPositionArg(string $name, bool $optional, array $other) : BaseArgument
    {
        return new BlockPositionArgument($name, $optional);
    }

    /**
     * @param mixed[] $other
     * @throws RegistrationException
     */
    public static function stringEnumArgument(string $name, bool $optional, array $other) : BaseArgument
    {
        foreach ($other as $v) {
            if (!is_scalar($v)) {
                throw new RegistrationException("Other config of string enum arg '$name' is not array<int|string, scalar>");
            }
        }
        /**
         * @phpstan-var array<scalar, scalar> $other
         */

        return new StringEnum($name, $optional, $other); // @phpstan-ignore-line TODO: string enum.
    }

        /**
     * @param mixed[] $other
     * @throws RegistrationException Subcommand cannot contain another subcommand.
     */
    public static function subCommand(string $name, bool $optional, array $other) : BaseSubCommand
    {
        $sub = self::subCommandNoLink($name, $optional, $other);
        if (!$sub instanceof HyundaiSubCommand) throw new RegistrationException("Cannot get subcommand config from " . $sub::class);
        $config = $sub->config;
        if ($config->link) {
            $argsss = $sub->getArgumentList();
            $args = [];
            foreach ($argsss as $argss) {
                foreach ($argss as $arg) {
$args[] = $arg; // Commando very weird??? hmm
                }
            } 
                $link = new HyundaiCommand($sub, $args);
                $link->logRegister();
        }

        return $sub;
    }

    /**
     * @param mixed[] $other
     * @throws RegistrationException Subcommand cannot contain another subcommand.
     */
    public static function subCommandNoLink(string $name, bool $optional, array $other) : BaseSubCommand
    {
        try {
            $config = SubCommandConfig::unmarshal($other);
        } catch (GeneralMarshalException|UnmarshalException $err) {
            throw new RegistrationException("Error when parsing config of subcommand '$name': " . $err->getMessage());
        }
        $sub = new HyundaiSubCommand($name, $config->description, $config->aliases);
        $sub->setPermission($config->permission);
        $sub->config = $config;

        ksort($config->args);
        $config->args = array_values($config->args);
        foreach ($config->args as $i => $argConfig) {
            $arg = HyundaiCommand::configToArg($argConfig);
            if ($arg instanceof BaseSubCommand) {
                throw new RegistrationException("Subcommand '$name' cannot contain another subcommand");
            }
            try {
                $sub->registerArgument($i, $arg);
            } catch (ArgumentOrderException $err) {
                throw new RegistrationException("Bad argument order for subcommand '$name': " . $err->getMessage());
            }
        }

        return $sub;
    }
}