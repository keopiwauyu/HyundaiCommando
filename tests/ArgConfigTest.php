<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use CortexPE\Commando\args\BlockPositionArgument;
use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\args\FloatArgument;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\args\Vector3Argument;
use PHPUnit\Framework\TestCase;

class ArgConfigTest extends TestCase {

	public function testUnmarshal() : void {
		$data = [
			"type" => "홍콩을 해방하다",
			"optional" => true,
			"name" => "우리 시대의 혁명",
			"other" => []
		];

		$config = ArgConfig::unmarshal($data);
		$this->assertSame($config->marshal(), $this->configProvider()->marshal());
	}

	private function configProvider() : ArgConfig {
return new ArgConfig(
			type: "홍콩을 해방하다",
			optional: true,
			name: "우리 시대의 혁명",
			other: []
		);
	}

	private function configProviderWithType(string $type) : ArgConfig {
		$config = $this->configProvider();
		$config->type = $type;

		return $config;
	}

	public function testConfigToArgUnknownType() : void {
		HyundaiCommand::resetArgTypes();
		$this->expectException(RegistrationException::class);
		HyundaiCommand::configToArg($this->configProvider());
	}

	public function testConfigtoArg() : void {
		HyundaiCommand::resetArgTypes();
		foreach ([
			"Boolean" => BooleanArgument::class,
			"Integer" => IntegerArgument::class,
			"Float" => FloatArgument::class,
			"RawString" => RawStringArgument::class,
			"Text" => TextArgument::class,
			"Vector3" => Vector3Argument::class,
			"BlockPosition" => BlockPositionArgument::class
			// TODO: sitrng enum teste.
		] as $type => $class) {
			$arg = HyundaiCommand::configToArg($config = $this->configProviderWithType($type));

			$this->assertSame($arg::class, $class);
			$this->assertSame($arg->getName(), $config->name);
			$this->assertSame($arg->isOptional(), $config->optional);
		}
	}
}