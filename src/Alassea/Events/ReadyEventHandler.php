<?php

namespace Alassea\Events;

class ReadyEventHandler extends AbstractEventHandler {
	public function handle(...$args) {
		$this->logger->info ( "AlasseaBot is ready! ", [ 
				"times" => $this->bot->getRestartCount ()
		] );
	}
}