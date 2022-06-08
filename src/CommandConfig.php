<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

class CommandConfig {

	/**
	 * @param array<string, ArgConfig> $args Key = arg display name.
	 */
	public function __construct(
		public array $args
	) {
	}

}