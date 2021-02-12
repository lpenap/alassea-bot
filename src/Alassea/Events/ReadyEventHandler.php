<?php

namespace Alassea\Events;

class ReadyEventHandler extends AbstractEventHandler {
	public function handle(...$args) {
		$this->bot->getLogger ()->info ( "AlasseaBot is ready! ", [ 
				"times" => $this->bot->getRestartCount ()
		] );
	}
}