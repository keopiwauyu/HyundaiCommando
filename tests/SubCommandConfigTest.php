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
			, "args" => []
];
	}

	private function setArgsInRandomIndexOrder(array $data, bool $stringIndex, bool $includeSubCommand) : array {
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
			$arg["other"] = $arg["type"] === "SubCommand" ? $this->setArgsInRandomIndexOrder($this->dataProvider(), false, false) : [];
		}
		$data["args"] = $args;

		return $data;
	}

	public function testUnmarshal() : void {
		$data = $this->setArgsInRandomIndexOrder($this->dataProvider(), false, true);
		$config = SubCommandConfig::unmarshal($data);
		$this->assertSame($config->marshal(), $data);
	}

	private function wrapWithArgConfigData(array $data) : array {
		return [
			"type" => "SubCommand",
			"optional" => true,
			"name" => "world",
			"other" => $data
		];
	}

/*	public function testConfigToArgStringIndex() : void {
		$this->expectException(RegistrationException::class);
		HyundaiCommand::configToArg(ArgConfig::unmarshal($this->wrapWithArgConfigData($this->setArgsInRandomIndexOrder($this->dataProvider(), true, false))));
	}
*/	
	public function testConfigToArgIncludeSubCommand() : void {
		$this->expectException(RegistrationException::class);
		HyundaiCommand::configToArg(ArgConfig::unmarshal($this->wrapWithArgConfigData($this->setArgsInRandomIndexOrder($this->dataProvider(), false, true))));
	}

	public function testConfigToArg() : void {
		$sub = HyundaiCommand::configToArg(ArgConfig::unmarshal($this->wrapWithArgConfigData($this->dataProvider())));

		$this->assertSame(HyundaiSubCommand::class, $sub::class);
		$this->assertSame(count($sub->getArgumentList()), 0);
	}

	public function testConfigToArgIncludeArgs() : void {
		$config = ArgConfig::unmarshal($this->wrapWithArgConfigData($this->setArgsInRandomIndexOrder($this->dataProvider(), false, false)));
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

	// TODO: Optional arg after noraml raarg
	// TODO: bad arg order like 0, 1 , 50, 390159240897
}