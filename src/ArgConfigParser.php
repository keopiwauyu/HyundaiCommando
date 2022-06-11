<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use libMarshal\parser\ArrayParseable;

/**
 * @implements ArrayParseable<array<int|string, ArgConfig>>
 */
class ArgConfigParser implements ArrayParseable {

	public function parse(mixed $value) : mixed {
		$args = [];
		foreach ($value as $k => $v) {
$args[$k] =  ArgConfig::unmarshal($v);
		}

		return $args;
	}

	public function serialize(mixed $value) : array {
		$data = [];
		foreach ($value as $k => $v) {
			$data[$k] = $v->marshal();
		}
		
		return $data;
	}
}