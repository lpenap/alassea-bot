<?php

namespace Alassea\Commands\Core;

use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Alassea\Commands\AbstractCommand;

class HelpCommand extends AbstractCommand {
	public function run(array $params): void {
		$embed = $this->getDiscord ()->factory ( Embed::class, [ 
				"title" => "AlasseaBot Help",
				"color" => '#0099ff'
		], true );
		foreach ( $this->getBot ()->getCommandsHelp () as $cmd => $helpText ) {
			$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
					"name" => $cmd,
					"value" => $helpText,
					"inline" => false
			] ) );
		}
		$this->getMessage ()->channel->sendMessage ( "", false, $embed )->then ( function (Message $message) {
			$this->getLogger ()->debug ( "HelpCommand: Help info sent!" );
		} )->otherwise ( function (\Exception $e) {
			$this->getLogger ()->error ( 'HelpCommand: Error sending message: ' . $e->getMessage () );
		} );
	}
	public function getHelpText(): string {
		return "Prints help information";
	}
}