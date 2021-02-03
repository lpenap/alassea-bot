<?php

namespace Alassea\Commands\System;

use Alassea\Commands\AbstractCommand;

class EchoCommand extends AbstractCommand {
	protected $str;
	public function run($params) {
		$this->getMessage ()->reply ( 'Echo... echo!!! : ' . $this->str );
	}
	public function prepare($params) {
		$this->str = implode ( ", ", $params );
	}
}