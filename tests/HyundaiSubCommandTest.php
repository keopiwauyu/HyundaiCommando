<?php

declare(strict_types=1);

namespace keopiwauyu\HyundaiCommando;

use PHPUnit\Framework\TestCase;
use keopiwauyu\HyundaiCommando\Cmds\FakeCommandSender;
use keopiwauyu\HyundaiCommando\Cmds\UpdateArrayOnExecute;

class HyundaiSubCommandTest extends TestCase {
	public function testExecute() : void {
		HyundaiCommand::resetArgTypes();
		$update = [];
		$cmd = HyundaiCommand::createForTesting(UpdateArrayOnExecute::make($update), false, false);
		$cmd->execute(new FakeCommandSender(), "hello", []);
		$this->assertSame($update, []);
	}
}