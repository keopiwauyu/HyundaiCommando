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

class SubCommandConfigTest extends TestCase {

	private function dataProvider() : array {
return [
			"description" => "우린 그렇게 두렵다",
			"permission" => "우린 그도록 애절하다",
			"aliases" => [
				"고개 들면서 구호 외치면서",
				"자유는 다시 오길"
			]
			, "args" => [],
			"links" => []
];
	}

	/**
	 * @param mixed[] $data
	 * @param string[] $depends
	 */
	private function setArgsInRandomIndexOrder(array $data, bool $stringIndex, bool $includeSubCommand, array $depends) : array {
		$args = [
			1 => ["type" => "Boolean"],
			3 => ["type" => "Integer"],
			6 => ["type" => "Float"],
			4 => ["type" => "RawString"],
			8 => ["type" => "Text"], // ONLY 8!!!! OTHERE THING === ERROR!!!!!! TODO: make ateest.
			0 => ["type" => "RawString"],
			2 => ["type" => "Vector3"],
			5 => ["type" => "BlockPosition"]
		];
		if (count($args) !== 8) {
			throw new \RuntimeException("Sample args count is not 8");
		}

		if ($includeSubCommand) {
			$args[9] = ["type" => "SubCommand"];
		}
		if ($stringIndex) {
			$clone = $args;
			$args = [];
		foreach ($clone as $k => $v) {
			$args[$k . "팔천구백육십사"] = $v;
		}
		}

		foreach ($args as &$arg) {
			$arg["optional"] = true;
			$arg["name"] = "고개 들면서 구호 외치면서";
			$arg["depends"] = $depends;
			$arg["other"] = $arg["type"] === "SubCommand" ? $this->setArgsInRandomIndexOrder($this->dataProvider(), false, false, []) : [];
		}
		$data["args"] = $args;

		return $data;
	}

	public function testUnmarshal() : void {
		$data = $this->setArgsInRandomIndexOrder($this->dataProvider(), false, true, []);
		$config = SubCommandConfig::unmarshal($data);
		$this->assertSame($data, $config->marshal());
	}

	private function wrapWithArgConfigData(array $data) : array {
		return [
			"type" => "SubCommand",
			"optional" => true,
			"name" => "world",
			"depends" => [],
			"other" => $data
		];
	}

/*	public function testConfigToArgStringIndex() : void {
		$this->expectException(RegistrationException::class);
		HyundaiCommand::configToArg(ArgConfig::unmarshal($this->wrapWithArgConfigData($this->setArgsInRandomIndexOrder($this->dataProvider(), true, false, []))));
	}
*/	
	public function testConfigToArgIncludeSubCommandUnknownDepend() : void {
		$this->expectException(RegistrationException::class);
		$config = ArgConfig::unmarshal($this->wrapWithArgConfigData($this->setArgsInRandomIndexOrder($this->dataProvider(), false, true, ["서서히 자유를 위해 큰힘을 모여"])));
		HyundaiCommand::configToArg($config);
	}

	public function testConfigToArgIncludeSubCommand() : void {
		$this->expectException(RegistrationException::class);
		HyundaiCommand::configToArg(ArgConfig::unmarshal($this->wrapWithArgConfigData($this->setArgsInRandomIndexOrder($this->dataProvider(), false, true, []))));
	}


	public function testConfigToArg() : void {
		$sub = HyundaiCommand::configToArg(ArgConfig::unmarshal($this->wrapWithArgConfigData($this->dataProvider())));

		$this->assertSame(HyundaiSubCommand::class, $sub::class);
		$this->assertSame(count($sub->getArgumentList()), 0);
	}

	public function testConfigToArgIncludeArgs() : void {
		$config = ArgConfig::unmarshal($this->wrapWithArgConfigData($this->setArgsInRandomIndexOrder($this->dataProvider(), false, false, [])));
		$sub = HyundaiCommand::configToArg($config);

		$this->assertSame(HyundaiSubCommand::class, $sub::class);

		$args = $config->other["args"];
		ksort($args);
		$args = array_values($args);
		$subArgs = $sub->getArgumentList();
		$argClassToFactory = array_flip(ArgConfigTest::ARG_FACTORY_TO_CLASS);
		foreach ($args as $i => $arg) {
			$subArg = $subArgs[$i][0];
		$this->assertSame($subArg->getName(), $arg["name"]);
		$this->assertSame($subArg->isOptional(), $arg["optional"]);
		$this->assertSame($argClassToFactory[$subArg::class], $arg["type"]);
		}
	}

	public function testConfigToArgNormalArgAfterOptionalArg() : void {
		$data = $this->dataProvider();
		$data["args"][0] = ["type" => "Boolean", "name" => "자유는 다시 오길", "optional" => true, "depends" => [], "other" => []];
		$data["args"][1] = ["type" => "Boolean", "name" => "총알 눈앞에 지나가", "optional" => false, "depends" => [], "other" => []];

		$this->expectException(RegistrationException::class);
		HyundaiCommand::configToArg(ArgConfig::unmarshal($this->wrapWithArgConfigData($data)));
	}
}