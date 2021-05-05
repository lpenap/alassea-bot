<?php

namespace Alassea\Commands;

use Psr\Log\LoggerInterface;
use Alassea\Alassea;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;
use Alassea\Database\CacheInterface;
use Alassea\Database\Cache;

/**
 * Abstract class to be extended by all command classes.
 *
 * This class provides boilerplate code to command implementations
 * in which they only need to implement the run() function, and in some cases,
 * override prepare() and cleanup()
 *
 * @author luis
 *        
 */
abstract class AbstractCommand implements CommandInterface {
	protected $discord;
	protected $message;
	protected $params;
	protected $bot;
	protected $logger;
	protected $cache = null;
	/**
	 * Optional function called on the first phase of command processing, overrided by subclasses.
	 *
	 * {@inheritdoc}
	 * @see \Alassea\Commands\CommandInterface::prepare()
	 */
	public function prepare(array $params): void {
	}
	/**
	 * Optional function called on the third phase of command processing, overrided by subclasses.
	 *
	 * {@inheritdoc}
	 * @see \Alassea\Commands\CommandInterface::cleanup()
	 */
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
	public function addField(Embed &$embed, string $fieldName, string $fieldValue, bool $inline) {
		$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
				"name" => $fieldName,
				"value" => $fieldValue,
				"inline" => $inline
		] ) );
	}
	public function getCacheContextName(): string {
		// TODO improve this
		$parts = explode ( '\\', get_called_class () . "_cache" );
		return array_pop ( $parts );
	}
	public function getCache(): CacheInterface {
		$this->logger->debug ( "AbstractCommand: Using cache context: " . $this->getCacheContextName () );
		if ($this->cache == null) {
			$this->cache = new Cache ( $this->getCacheContextName () );
		}
		return $this->cache;
	}
}