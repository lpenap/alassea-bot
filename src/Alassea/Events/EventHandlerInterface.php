<?php

namespace Alassea\Events;

use Alassea\Alassea;
use Discord\Discord;

interface EventHandlerInterface {
	public function setup(string $event, Discord $discord, Alassea $bot);
	public function handle(...$args);
}

