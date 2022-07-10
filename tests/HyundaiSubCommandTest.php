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
		$cmd->execute(new FakeCommandSender(), "hello", ["bbb"]);
		$this->assertSame([array_values($cmd->getSubCommands())[0]->getName()], $update);
	}

		public function testExecuteRegisterArgs() : void {
		$update = [];
		$cmd = UpdateArrayOnExecute::makeHyundai($update, true, true);
		$args = ["aaa", "true", "3", "1.4587742654465", "world", "-1.4587742654465", ".0", "-.0", "100", "200", "300", "https://youtu.be/Bc8vc8Y_AYw"]; // TODO: test ~~~ in intragrated tesst
		$cmd->execute(new FakeCommandSender(), "hello", $args);

		$args[6] = $args[7] = "0";
		$this->assertSame($args, $update);
	}

}