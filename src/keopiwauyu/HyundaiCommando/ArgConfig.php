<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\BaseArgument;
use libMarshal\MarshalTrait;
use libMarshal\attributes\Field;

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
     * @var array<string, BaseArgument|BaseSubCommand>
     */
    public array $dependeds;

    /**
     * @throws RegistrationException
     */
    public function getDepend(string $name) : BaseArgument|BaseSubCommand {
        return $this->depended[$name] ?? throw new RegistrationException("'" . $this->name . "' has unknown depend: $name");
    }
}