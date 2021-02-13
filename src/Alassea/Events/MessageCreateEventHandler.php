<?php

namespace Alassea\Events;

use Alassea\Commands\CommandManager;

class MessageCreateEventHandler extends AbstractEventHandler {
	public function handle(...$args) {
		if (! isset ( $args [0] ) || $args [0] == null) {
			$this->logger->warning ( "Received a null message? this maybe a problem..." );
			return;
		}
		$message = $args [0];
		$prefix = $this->bot->getPrefs ()->get ( 'prefix' );
		if ($message->content !== "" && $message->content [0] == $prefix) {
			$this->logger->debug ( "Message received: ", [ 
					"author" => $message->author->username,
					"msg" => $message->content
			] );
			$content = preg_replace ( "/\s+/", " ", strtolower ( $message->content ) );
			$params = explode ( " ", $content );
			$cmd = ltrim ( array_shift ( $params ), $prefix );
			CommandManager::instance ()->executeCommand ( $cmd, $params, $message );
		}
	}
}

