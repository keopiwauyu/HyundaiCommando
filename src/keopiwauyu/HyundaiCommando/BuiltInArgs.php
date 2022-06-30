<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\args\BlockPositionArgument;
use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\args\FloatArgument;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\args\Vector3Argument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use libMarshal\exception\GeneralMarshalException;
use libMarshal\exception\UnmarshalException;
use function array_values;
use function is_scalar;
use function ksort;

class BuiltInArgs
{

    /**
     * @param array<string, BaseArgument|BaseSubCommand> $depends
     */
    public static function booleanArg(ArgConfig $config) : BaseArgument
    {
        return new BooleanArgument($config->name, $config->optional);
    }

    /**
     * @param array<string, BaseArgument|BaseSubCommand> $depends
     */
    public static function integerArg(ArgConfig $config) : BaseArgument
    {
        return new IntegerArgument($config->name, $config->optional);
    }

    /**
     * @param array<string, BaseArgument|BaseSubCommand> $depends
     */
    public static function floatArg(ArgConfig $config) : BaseArgument
    {
        return new FloatArgument($config->name, $config->optional);
    }

    /**
     * @param array<string, BaseArgument|BaseSubCommand> $depends
     */
    public static function rawStringArg(ArgConfig $config) : BaseArgument
    {
        return new RawStringArgument($config->name, $config->optional);
    }

    /**
     * @param array<string, BaseArgument|BaseSubCommand> $depends
     */
    public static function textArg(ArgConfig $config) : BaseArgument
    {
        return new TextArgument($config->name, $config->optional);
    }

    /**
     * @param array<string, BaseArgument|BaseSubCommand> $depends
     */
    public static function vector3Arg(ArgConfig $config) : BaseArgument
    {
        return new Vector3Argument($config->name, $config->optional);
    }

    /**
     * @param array<string, BaseArgument|BaseSubCommand> $depends
     */
    public static function blockPositionArg(ArgConfig $config) : BaseArgument
    {
        return new BlockPositionArgument($config->name, $config->optional);
    }

    /**
     * @param array<string, BaseArgument|BaseSubCommand> $depends
     * @throws RegistrationException
     */
    public static function stringEnumArgument(ArgConfig $config) : BaseArgument
    {
        foreach ($config->other as $v) {
            if (!is_scalar($v)) {
                throw new RegistrationException("Other config of string enum arg '$name' is not array<int|string, scalar>");
            }
        }

        throw new \RegistrationException("String enum arg is working in progress");
    }

    /**
     * @param array<string, BaseArgument|BaseSubCommand> $depends
     * @throws RegistrationException Subcommand cannot contain another subcommand.
     */
    public static function subCommand(ArgConfig $config) : BaseSubCommand
    {
        $sub = self::subCommandNoLink($config);
        if (!$sub instanceof HyundaiSubCommand) {
            throw new RegistrationException("Cannot get subcommand config from " . $sub::class);
        }
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
     * @param array<string, BaseArgument|BaseSubCommand> $depends
     * @throws RegistrationException Subcommand cannot contain another subcommand.
     */
    public static function subCommandNoLink(ArgConfig $config) : BaseSubCommand
    {
        $other = $config->other;
        $name = $config->name;
        try {
            $subConfig = SubCommandConfig::unmarshal($other);
        } catch (GeneralMarshalException|UnmarshalException $err) {
            throw new RegistrationException("Error when parsing subConfig of subcommand '$name': " . $err->getMessage());
        }
        $sub = new HyundaiSubCommand($name, $subConfig->description, $subConfig->aliases);
        $sub->setPermission($subConfig->permission);
        $sub->config = $subConfig;

        ksort($subConfig->args);
        $subConfig->args = array_values($subConfig->args);
        foreach ($subConfig->args as $i => $argConfig) {
            if (is_string($argConfig)) $arg = $config->getDepend($argConfig);
            else {
            $arg = HyundaiCommand::configToArg($argConfig);
            if ($arg instanceof BaseSubCommand) {
                throw new RegistrationException("Subcommand '$name' cannot contain another subcommand");
            }
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