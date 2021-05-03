<?php

namespace Alassea\Commands\Core;

use Alassea\Preferences;
use Alassea\Commands\AbstractCommand;
use Alassea\Utils\DateTimeUtils;
use Discord\Discord;
use Discord\Parts\Embed\Embed;

class InfoCommand extends AbstractCommand {
	public function run(array $params): void {
		$embed = $this->getDiscord ()->factory ( Embed::class, [ 
				"title" => "AlasseaBot Info",
				"color" => '#0099ff'
		], true );
		$this->addField ( $embed, "AlasseaBot", Preferences::VERSION, true );
		$this->addField ( $embed, "Restarts", $this->getBot ()->getRestartCount (), true );
		$this->addField ( $embed, "Uptime", DateTimeUtils::getTimeAgo ( $this->getBot ()->getUptime () ), true );
		$this->addField ( $embed, "Discord Versions (Gateway/API/DiscordPHP)", Discord::GATEWAY_VERSION . ' / ' . Discord::HTTP_API_VERSION . ' / ' . Discord::VERSION, false );
		$this->addField ( $embed, "Project Home", 'https://github.com/lpenap/alassea-bot', false );
		$this->sendMessageSimple ( "", $embed );
	}
	public function getHelpText(): string {
		return "Prints bot information";
	}
}