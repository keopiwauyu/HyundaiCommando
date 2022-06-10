<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use libMarshal\MarshalTrait;
use libMarshal\attributes\Field;

class ArgConfig {
	use MarshalTrait;

	/**
	 * @param mixed[] $other
	 */
	public function __construct(
		#[Field] public string $type,
		#[Field] public bool $optional,
		#[Field] public string $name, // TODO: support langusges??
		#[Field] public array $other
	) {
	}
}