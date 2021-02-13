<?php

namespace Alassea\Commands\Core;

use Alassea\Commands\AbstractCommand;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Alassea\Commands\CommandManager;

class HelpCommand extends AbstractCommand {
	public function run(array $params): void {
		$embed = $this->getDiscord ()->factory ( Embed::class, [ 
				"title" => "AlasseaBot Help",
				"color" => '#0099ff'
		], true );
		$cmdHelp = (CommandManager::instance ())->getCommandsHelp ();
		foreach ( $cmdHelp as $cmd => $helpText ) {
			$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
					"name" => $cmd,
					"value" => $helpText,
					"inline" => false
			] ) );
		}
		$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
				"name" => "More Info",
				"value" => 'https://github.com/lpenap/alassea-bot',
				"inline" => false
		] ) );
		$this->sendMessageSimple ( "", $embed );
	}
	public function getHelpText(): string {
		return "Prints help information";
	}
}