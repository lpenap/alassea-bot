<?php

namespace Alassea\Commands\Core;

use Alassea\Commands\AbstractCommand;
use Alassea\Commands\CommandManager;
use Discord\Parts\Embed\Embed;

class HelpCommand extends AbstractCommand {
	public function run(array $params): void {
		$embed = $this->getDiscord ()->factory ( Embed::class, [ 
				"title" => "AlasseaBot Help",
				"color" => '#0099ff'
		], true );
		$cmdHelp = (CommandManager::instance ())->getCommandsHelp ();
		foreach ( $cmdHelp as $cmd => $helpText ) {
			$this->addField ( $embed, $cmd, $helpText, false );
		}
		$this->addField ( $embed, "More Info", 'https://github.com/lpenap/alassea-bot', false );
		$this->sendMessageSimple ( "", $embed );
	}
	public function getHelpText(): string {
		return "Prints help information";
	}
}