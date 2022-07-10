<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\args\BaseArgument;
use CortexPE\Commando\BaseSubCommand;
use Exception;
use libMarshal\attributes\Field;
use libMarshal\MarshalTrait;
use function implode;
use function in_array;

class ArgConfig
{
    use MarshalTrait;

    /**
     * @param mixed[] $other
     * @param string[] $depends
     */
    public function __construct(
        #[Field] public string $type,
        #[Field] public bool $optional,
        #[Field] public string $name, // TODO: support langusges??
        #[Field] public array $depends,
        #[Field] public array $other
    ) {
    }

    /**
     * @var array<BaseArgument|BaseSubCommand>
     */
    public array $dependeds;

    /**
     * @throws RegistrationException
     */
    public function getDepend(string $name) : BaseArgument|BaseSubCommand
    {
        return $this->dependeds[$name] ?? throw new RegistrationException("'" . $this->name . "' has unknown depend: $name");
    }

    /**
     * @param array<string, ArgConfig> $configs
     * @param string[] $orders
     * @param string[] $trace
     * @throws Exception
     */
    public static function arrangeLoadOrder(array $configs,array &$orders, string $name, array $trace) : void
    {
        $oldTrace = $trace;
        $trace[] = $name;
        if (in_array($name, $oldTrace, true)) {
            throw new Exception("'$name': recursive depend ('" . implode("' => '", $trace) . "')");
        }
        if (in_array($name, $orders, true)) {
            return;
        }
        $config = $configs[$name];
        foreach ($config->depends as $depend) {
            self::arrangeLoadOrder($configs, $orders, $depend, $trace);
        }
        $orders[] = $name;
    }
}