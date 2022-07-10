<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use PHPUnit\Framework\TestCase;
use keopiwauyu\HyundaiCommando\Cmds\FakeCommandSender;
use keopiwauyu\HyundaiCommando\Cmds\UpdateArrayOnExecute;
use pocketmine\math\Vector3;

class HyundaiCommandTest extends TestCase {
	public function testExecute() : void {
		$update = [];
		$cmd = UpdateArrayOnExecute::makeHyundai($update, false, false);
		$cmd->execute(new FakeCommandSender(), "hello", []);
		$this->assertSame([], $update);
	}

	public function testExecuteRegisterArgs() : void {
		$update = [];
		$cmd = UpdateArrayOnExecute::makeHyundai($update, true, false);
		// $cmd->execute(new FakeCommandSender(), "hello", [true, 3, 1458774265446520948527, "world", new Vector3(100, 200, 300), new Vector3(INF, NAN, -INF), "https://youtu.be/Bc8vc8Y_AYw"]);
		// $args = ["true", "3", "1.4587742654465", "world", "INF", "NAN", "-INF", "100", "200", "300", "https://youtu.be/Bc8vc8Y_AYw"]; // BRUH INF NAN sicentific notiatioN no aupported
		$args = ["true", "3", "1.4587742654465", "world", "-1.4587742654465", ".0", "-.0", "100", "200", "300", "https://youtu.be/Bc8vc8Y_AYw"]; // TODO: test ~~~ in intragrated tesst
		$cmd->execute(new FakeCommandSender(), "hello", $args);
		$args[5] = $args[6] = "0";
		$this->assertSame($args, $update);
	}
}