<?php

namespace Alassea\Commands\Custom;

use Alassea\Commands\AbstractCommand;

class EchoCommand extends AbstractCommand {
	protected $str;
	public function run(array $params): void {
		// command execution
		$this->getMessage ()->reply ( 'Echo!!! : ' . $this->str );

	/**
	 * Functions available:
	 * $this->getDiscord() : Gets the current Discord client.
	 * $this->getMesssage() : Gets the current Message obj.
	 * $this->getBot() : Gets reference to Alassea Bot.
	 * $this->getParams() : Gets the params array.
	 * $this->getLogger() : Gets the system logger (Psr\Log\LoggerInterface).
	 * $this->sendMessageSimple(string $text, Embed $embed = null) : Function for easy message sending.
	 */
	}
	public function prepare(array $params): void {
		// preparing command execution
		$this->str = implode ( " ", $params );
	}
	public function cleanup(): void {
		// cleanup after running
	}
	public function getHelpText(): string {
		return "Replies with the received text";
	}
}