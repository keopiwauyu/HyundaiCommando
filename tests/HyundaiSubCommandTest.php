<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use PHPUnit\Framework\TestCase;
use keopiwauyu\HyundaiCommando\Cmds\FakeCommandSender;
use keopiwauyu\HyundaiCommando\Cmds\UpdateArrayOnExecute;

class HyundaiSubCommandTest extends TestCase {
	public function testExecute() : void {
		$update = [];
		$cmd = UpdateArrayOnExecute::makeHyundai($update, false, true);
		$expected = ["bbb"];

		$cmd->execute(new FakeCommandSender(), "hello", ["bbb"]);
		$this->assertSame($expected, $update);

		$cmd->execute(new FakeCommandSender(), "hello", ["bbb"]);
		$this->assertSame($expected, $update);
	}

		public function testExecuteRegisterArgs() : void {
		$update = [];
		$cmd = UpdateArrayOnExecute::makeHyundai($update, true, true);
		$args = $expected = ["bbb", "true", "3", "1.4587742654465", "world", "-1.4587742654465", ".0", "-.0", "100", "200", "300", "https://youtu.be/Bc8vc8Y_AYw"]; // TODO: test ~~~ in intragrated tesst
		$expected[6] = $expected[7] = "0";

		$cmd->execute(new FakeCommandSender(), "hello", $args);
		$this->assertSame($expected, $update);

		$args[0] = "eee";
				$cmd->execute(new FakeCommandSender(), "hello", $args);
		$this->assertSame($expected, $update);
	}

}