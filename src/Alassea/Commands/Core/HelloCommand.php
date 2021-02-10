<?php

namespace Alassea\Commands\Core;

use Alassea\Commands\AbstractCommand;

class HelloCommand extends AbstractCommand {
	public function run(array $params): void {
		$this->getMessage ()->reply ( 'hello! I\'m Alassea, the friendly bot!' );
	}
	public function getHelpText(): string {
		return "Replies with a hello message";
	}
}