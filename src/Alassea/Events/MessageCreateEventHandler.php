<?php

namespace Alassea\Events;

class MessageCreateEventHandler extends AbstractEventHandler {
	public function handle(...$args) {
		if (! isset ( $args [0] ) || $args [0] == null) {
			$this->bot->getLogger ()->warning ( "Received a null message? this maybe a problem..." );
			return;
		}
		$message = $args [0];
		if ( $message->content !== "" && $message->content [0] == $this->bot->getPrefix ()) {
			$this->bot->getLogger ()->debug ( "Message received: ", [ 
					"author" => $message->author->username,
					"msg" => $message->content
			] );
			$content = preg_replace ( "/\s+/", " ", strtolower ( $message->content ) );
			$params = explode ( " ", $content );
			$cmd = ltrim ( array_shift ( $params ), $this->bot->getPrefix () );
			$this->bot->executeCommand ( $cmd, $params, $message );
		}
	}
}

