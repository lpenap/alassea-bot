<?php

namespace Alassea\Events;

class GuildMemberAddEventHandler extends AbstractEventHandler {
	public function handle(...$args) {
		if (! isset ( $args [0] ) || $args [0] == null) {
			return;
		}
		$member = $args [0];
		$this->logger ()->debug ( "New Member!: " . $member->username . ", data: " . $member->serialize () );
	}
}