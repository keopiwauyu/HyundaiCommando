<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use keopiwauyu\HyundaiCommando\RegistrationException;

class BuiltInArgs {

	/**
	 * @param mixed[] $other
	 */
	public static function booleanArg(string $name, bool $optional, array $other) : BaseArgument {
		return new StringArgument($name, $optional);
	}

	/**
	 * @param mixed[] $other
	 */
	public static function integerArg(string $name, bool $optional, array $other) : BaseArgument {
		return new IntegerArgument($name, $optional);
	}

	/**
	 * @param mixed[] $other
	 */
	public static function floatArg(string $name, bool $optional, array $other) : BaseArgument {
		return new FloatArgument($name, $optional);
	}

	/**
	 * @param mixed[] $other
	 */
	public static function rawStringArg(string $name, bool $optional, array $other) : BaseArgument {
		return new RawStringArgument($name, $optional);
	}

	/**
	 * @param mixed[] $other
	 */
	public static function textArgument(string $name, bool $optional, array $other) : BaseArgument {
		return new TextArgument($name, $optional);
	}

	/**
	 * @param mixed[] $other
	 */
	public static function vector3Arg(string $name, bool $optional, array $other) : BaseArgument {
		return new Vector3Argument($name, $optional);
	}

	/**
	 * @param mixed[] $other
	 */
	public static function blockPositionArg(string $name, bool $optional, array $other) : BaseArgument {
		return new BlockPositionArgument($name, $optional);
	}

	/**
	 * @param mixed[] $other
	 * @throw RegistrationException
	 */
	public static function stringEnumArgument(string $name, bool $optional, array $other) : BaseArgument {
		foreach ($other as $k => $v) if (!is_scalar($k) || !is_scalar($v)) throw new RegistrationException("Config for string enum argument should be array<scalar, scalar>");
		/**
		 * @phpstan-var array<scalar, scalar> $other
		 */
		
		return new StringEnum($name, $optional, $other);
	}
}