<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

class ArgConfig {

	/**
	 * @param mixed[] $other
	 */
	public function __construct(
		public string $type,
		public bool $optional,
		public string $name, // TODO: support langusges??
		public array $other
	) {
	}
}