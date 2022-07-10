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
use function is_string;
use function ksort;

class BuiltInArgs
{
    public static function booleanArg(ArgConfig $config) : BaseArgument
    {
        return new BooleanArgument($config->name, $config->optional);
    }

    public static function integerArg(ArgConfig $config) : BaseArgument
    {
        return new IntegerArgument($config->name, $config->optional);
    }

    public static function floatArg(ArgConfig $config) : BaseArgument
    {
        return new FloatArgument($config->name, $config->optional);
    }

    public static function rawStringArg(ArgConfig $config) : BaseArgument
    {
        return new RawStringArgument($config->name, $config->optional);
    }

    public static function textArg(ArgConfig $config) : BaseArgument
    {
        return new TextArgument($config->name, $config->optional);
    }

    public static function vector3Arg(ArgConfig $config) : BaseArgument
    {
        return new Vector3Argument($config->name, $config->optional);
    }

    public static function blockPositionArg(ArgConfig $config) : BaseArgument
    {
        return new BlockPositionArgument($config->name, $config->optional);
    }

    /**
     * @throws RegistrationException
     */
    public static function stringEnumArg(ArgConfig $config) : BaseArgument
    {
        $name = $config->name;
        foreach ($config->other as $v) {
            if (!is_scalar($v)) {
                throw new RegistrationException("Other config of string enum arg '$name' is not array<int|string, scalar>");
            }
        }

        throw new RegistrationException("String enum arg is working in progress");
    }

    /**
     * @throws RegistrationException Subcommand cannot contain another subcommand.
     */
    public static function subCommand(ArgConfig $config) : BaseSubCommand
    {
        $sub = self::subCommandNoLink($config);
        if (!$sub instanceof HyundaiSubCommand) {
            throw new \RuntimeException("Cannot get subcommand config from " . $sub::class);
        }
        $config = $sub->config;

        if ($config->links === []) return $sub;
            $argsss = $sub->getArgumentList();
            $args = [];
            foreach ($argsss as $argss) {
                foreach ($argss as $arg) {
                    $args[] = $arg; // Commando very weird??? hmm
                }
            }
            $sub->links = array_map(
                fn(string $link) => new HyundaiCommand($sub, $args, $link),
                $config->links
            );

        return $sub;
    }

    /**
     * @throws RegistrationException Subcommand cannot contain another subcommand.
     */
    public static function subCommandNoLink(ArgConfig $config) : BaseSubCommand
    {
        $other = $config->other;
        $name = $config->name;
        try {
            $subConfig = SubCommandConfig::unmarshal($other);
        } catch (GeneralMarshalException|UnmarshalException $err) {
            throw new RegistrationException("Error when parsing subcommand config: " . $err->getMessage());
        }
        $sub = new HyundaiSubCommand($name, $subConfig->description, $subConfig->aliases);
        $sub->setPermission($subConfig->permission);
        $sub->config = $subConfig;

        ksort($subConfig->args);
        $subConfig->args = array_values($subConfig->args);
        foreach ($subConfig->args as $i => $argConfig) {
            if (is_string($argConfig)) {
                $arg = $config->getDepend($argConfig);
            } else {
                try {
                foreach ($argConfig->depends as $id => $depend) {
$argConfig->dependeds[$id] = $config->dependeds[$id] ?? throw new RegistrationException("Unknown global arg '$id'");
                }
                $arg = HyundaiCommand::configToArg($argConfig);
                } catch (RegistrationException $err) {
                    throw new RegistrationException("Error when parsing arg '$i' in subcommand: " . $err->getMessage());
                }
            }
            if ($arg instanceof BaseSubCommand) {
                throw new RegistrationException("Subcommand cannot contain another subcommand");
            }
            try {
                $sub->registerArgument($i, $arg);
            } catch (ArgumentOrderException $err) {
                throw new RegistrationException("Bad argument order for subcommand" . $err->getMessage());
            }
        }

        return $sub;
    }
}
