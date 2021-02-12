<?php

namespace Alassea\Events;

use Alassea\Alassea;
use Discord\Discord;

abstract class AbstractEventHandler implements EventHandlerInterface {
	protected $discord;
	protected $bot;
	public function setup($event, Discord $discord, Alassea $bot) {
		$this->bot = $bot;
		$this->discord = $discord;
		$this->discord->on ( $event, $this );
	}
	public function __invoke(...$args) {
		$this->handle ( ...$args );
	}
}

