<?php

namespace Alassea\Events;

class GuildMemberRemoveEventHandler extends AbstractEventHandler {
	public function handle(...$args) {
		if ($args [0] == null) {
			return;
		}
		$member = $args [0];
		$this->logger->debug ( "Removing Member!: " . $member->username . ", data: " . $member->serialize () );
	}
}