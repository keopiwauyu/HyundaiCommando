<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use libMarshal\attributes\Field;
use libMarshal\MarshalTrait;

class SubCommandConfig
{
    use MarshalTrait;

    /**
     * @param string[] $aliases
     * @param ArgConfig[] $args Not guaranteeing that index is string and is in increasing order which starts at 0.
     */
    public function __construct(
        #[Field] public string $description, // TODO: support langusges??
        #[Field] public string $permission,
        #[Field] public array $aliases,
        #[Field(parser: ArgConfigParser::class)] public array $args // @phpstan-ignore-line
    ) {
    }
}