<?php

namespace Alassea\Commands\System;

use Alassea\Commands\AbstractCommand;
use Discord\Parts\Channel\Message;

class RestartCommand extends AbstractCommand {
	public function run($params) {
		$this->getMessage ()->channel->sendMessage ( "Pack your packs!, restarting!", false )->then ( function (Message $message) {
			$this->getBot ()->restart ();
		} );
	}
	public function getHelpText() {
		return "Restart the bot";
	}
}