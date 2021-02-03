<?php

namespace Alassea\Commands\System;

use Alassea\Commands\AbstractCommand;

class HelloCommand extends AbstractCommand {
	public function run($params) {
		$this->getMessage ()->reply ( 'hello! I\'m Alassea, the friendly bot!' );
	}
}