<?php

namespace Alassea\Commands\Core;

use Alassea\Commands\AbstractCommand;

class EchoCommand extends AbstractCommand {
	protected $str;
	public function run(array $params): void {
		$this->getMessage ()->reply ( 'Echo... echo!!! : ' . $this->str );
	}
	public function prepare(array $params): void {
		$this->str = implode ( ", ", $params );
	}
	public function getHelpText(): string {
		return "Replies with the received text";
	}
}