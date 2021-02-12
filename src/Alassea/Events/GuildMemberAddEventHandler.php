<?php

namespace Alassea\Events;

class GuildMemberAddEventHandler extends AbstractEventHandler {
	public function handle(...$args) {
		if ($args [0] == null) {
			return;
		}
		$member = $args [0];
		$this->bot->getLogger ()->debug ( "New Member!: " . $member->username . ", data: " . $member->serialize () );
	}
}