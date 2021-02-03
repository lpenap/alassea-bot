<?php

namespace Alassea\Commands\Custom;

use Alassea\Commands\AbstractCommand;

class EchoCommand extends AbstractCommand {
	protected $str;
	public function run($params) {
		// command execution
		$this->getMessage ()->reply ( 'Echo!!! : ' . $this->str );

	/**
	 * Functions available:
	 * $this->getDiscord() : Gets the current Discord client.
	 * $this->getMesssage() : Gets the current Message obj.
	 * $this->getBot() : Gets reference to Alassea Bot.
	 * $this->getParams() : Gets the params array.
	 */
	}
	public function prepare($params) {
		// preparing command execution
		$this->str = implode ( " ", $params );
	}
	public function cleanup() {
		// cleanup after running
	}
}