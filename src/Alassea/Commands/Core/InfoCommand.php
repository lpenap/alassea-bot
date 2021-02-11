<?php

namespace Alassea\Commands\Core;

use Alassea\Commands\AbstractCommand;
use Alassea\Utils\DateTimeUtils;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;

class InfoCommand extends AbstractCommand {
	public function run(array $params): void {
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
		$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
				"name" => "Project Home",
				"value" => 'https://github.com/lpenap/alassea-bot',
				"inline" => false
		] ) );
		$this->sendMessageSimple ( "", $embed );
	}
	public function getHelpText(): string {
		return "Prints bot information";
	}
}