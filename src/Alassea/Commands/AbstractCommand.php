<?php

namespace Alassea\Commands;

use Psr\Log\LoggerInterface;
use Alassea\Alassea;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;

abstract class AbstractCommand implements CommandInterface {
	protected $discord;
	protected $message;
	protected $params;
	protected $bot;
	protected $logger;
	public function prepare(array $params): void {
	}
	public function cleanup(): void {
	}
	public function setBot(Alassea $bot) {
		$this->bot = $bot;
	}
	public function setDiscord(Discord $discord) {
		$this->discord = $discord;
	}
	public function getBot(): Alassea {
		return $this->bot;
	}
	public function getMessage(): Message {
		return $this->message;
	}
	public function getDiscord(): Discord {
		return $this->discord;
	}
	public function setMessage(Message $message) {
		$this->message = $message;
	}
	public function getParams(): array {
		return $this->params;
	}
	public function setParams(array $params) {
		$this->params = $params;
	}
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}
	public function getLogger(): LoggerInterface {
		return $this->logger;
	}
	public function sendMessageSimple(string $text, Embed $embed = null) {
		$this->message->channel->sendMessage ( $text, false, $embed )->then ( function (Message $message) {
			$this->logger->debug ( "AbstractCommand: sendMessageSimple: sent!" );
		} )->otherwise ( function (\Exception $e) {
			$this->logger->error ( 'AbstractCommand: sendMessageSimple: Error sending message!: ' . $e->getMessage () );
		} );
	}
}