<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use libMarshal\MarshalTrait;
use libMarshal\attributes\Field;
use libMarshal\parser\ArrayParseable;

/**
 * @implements ArrayParseable<self>
 */
class ArgConfig implements ArrayParseable {
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

	public function parse(mixed $value) : mixed {
		return self::unmarshal($value);
	}

	public function serialize(mixed $value) : array {
		return $value;
	}
}