<?php

namespace Alassea\Events;

use Alassea\Alassea;
use Discord\Discord;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;

abstract class AbstractEventHandler implements EventHandlerInterface {
	protected $discord;
	protected $bot;
	protected $logger;
	public function setup($event, Discord $discord, Alassea $bot) {
		$this->bot = $bot;
		$this->discord = $discord;
		$this->discord->on ( $event, $this );
		$this->logger = new Monolog ( get_class ( $this ) );
		$this->logger->pushHandler ( new StreamHandler ( 'php://stdout', $this->bot->getPrefs ()->get ( 'log_level' ) ) );
	}
	public function __invoke(...$args) {
		$this->handle ( ...$args );
	}
}

