<?php

namespace Alassea\Commands\System;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Alassea\Commands\AbstractCommand;
use Alassea\Utils\DateTimeUtils;

class InfoCommand extends AbstractCommand {
	public function run($params) {
		$embed = $this->getDiscord ()->factory ( Embed::class, [ 
				"title" => "AlasseaBot Info",
				"color" => '#0099ff'
		], true );
		$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
				"name" => "AlasseaBot",
				"value" => $this->getBot ()::VERSION,
				"inline" => true
		] ) );
		$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
				"name" => "Restarts",
				"value" => $this->getBot ()->getRestartCount (),
				"inline" => true
		] ) );
		$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
				"name" => "Discord (Gateway/API/Client)",
				"value" => Discord::GATEWAY_VERSION . ' / ' . Discord::HTTP_API_VERSION . ' / ' . Discord::VERSION,
				"inline" => false
		] ) );
		$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
				"name" => "Uptime",
				"value" => DateTimeUtils::getTimeAgo ( $this->getBot ()->getUptime () ),
				"inline" => false
		] ) );
		$this->getMessage ()->channel->sendMessage ( "", false, $embed )->then ( function (Message $message) {
			$this->getLogger ()->debug ( "InfoCommand: Info sent!" );
		} )->otherwise ( function (\Exception $e) {
			$this->getLogger ()->error ( 'InfoCommand: Error sending message: ' . $e->getMessage () );
		} );
	}
	public function getHelpText() {
		return "Prints bot information";
	}
}