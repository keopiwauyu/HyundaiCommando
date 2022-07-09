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
			"type" => "hello",
			"optional" => true,
			"name" => "world",
			"depends" => [],
			"other" => []
		];

		$config = ArgConfig::unmarshal($data);
		$this->assertSame($config->marshal(), $this->configProvider()->marshal());
	}

	private function configProvider() : ArgConfig {
return new ArgConfig(
			type: "hello",
			optional: true,
			name: "world",
			depends: [],
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

	public function testConfigToArg() : void {
		HyundaiCommand::resetArgTypes();
		foreach (self::ARG_FACTORY_TO_CLASS as $type => $class) {
			$arg = HyundaiCommand::configToArg($config = $this->configProviderWithType($type));

			$this->assertSame($arg::class, $class);
			$this->assertSame($arg->getName(), $config->name);
			$this->assertSame($arg->isOptional(), $config->optional);
		}
	}

	public const ARG_FACTORY_TO_CLASS = [
			"Boolean" => BooleanArgument::class,
			"Integer" => IntegerArgument::class,
			"Float" => FloatArgument::class,
			"RawString" => RawStringArgument::class,
			"Vector3" => Vector3Argument::class,
			"BlockPosition" => BlockPositionArgument::class,
			"Text" => TextArgument::class // ENEED TO PUT BEHIND ALL THINGS BECAUSE AERROR!!!!
			// TODO: sitrng enum teste.
	];

	public function testGetUnknownDepend() : void {
		$this->expectException(RegistrationException::class);
		$this->configProvider()->getDepend("kjsadahiua");
	}

		public function testArrangeLoadOrderRecursive() : void {
			$configs = [
				"two" => $two = $this->configProviderWithType("Boolean"),
				"one" => $one = $this->configProviderWithType("Boolean"),
				"three" => $three = $this->configProviderWithType("Boolean")
			];
			$one->depends = ["two", "three"];
			$two->depends = ["three", "one"];
			$three->depends = ["one", "two"];

		$this->expectException(\Exception::class);
		$orders = [];
		foreach ($configs as $name => $config)ArgConfig::arrangeLoadOrder($configs, $orders, $name,[]);
		}

		public function testArrangeLoadOrderIndirectDepend() : void {
			$configs = [
				"four" => $four = $this->configProviderWithType("Boolean"),
				"two" => $two = $this->configProviderWithType("Boolean"),
				"one" => $one = $this->configProviderWithType("Boolean"),
				"three" => $three = $this->configProviderWithType("Boolean")
			];
			$one->depends = ["two"];
			$three->depends = ["one", "two"];
			$four->depends = ["three"];

		$orders = [];
		foreach ($configs as $name => $config)ArgConfig::arrangeLoadOrder($configs, $orders, $name,[]);

		$this->assertSame([
			"two",
			"one",
			"three",
			"four"
		], $orders);
	}

		public function testArrangeLoadOrderDirectDepend() : void {
			$configs = [
				"two" => $two = $this->configProviderWithType("Boolean"),
				"one" => $one = $this->configProviderWithType("Boolean"),
			];
			$two->depends = ["one"];

		$orders = [];
		foreach ($configs as $name => $config)ArgConfig::arrangeLoadOrder($configs, $orders, $name,[]);

		$this->assertSame([
			"one",
			"two"
		], $orders);
	}
}