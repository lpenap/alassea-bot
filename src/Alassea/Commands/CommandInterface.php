<?php

namespace Alassea\Commands;

use Psr\Log\LoggerInterface;
use Alassea\Alassea;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Alassea\Database\CacheInterface;

/**
 * Interface implemented by all commands in this bot.
 *
 * An additional Abstract class with boilerplate is also provided to simplify development.
 *
 * @author luis
 * @see \Alassea\Commands\AbstractCommand
 *
 */
interface CommandInterface {
	/**
	 * First stage of command processing, parsed and trimed params are passed as an array.
	 *
	 * @param array $params
	 *        	Array of words written after the command.
	 */
	public function prepare(array $params): void;
	/**
	 * Second stage of command processing, here is where the command is executed.
	 *
	 * @param array $params
	 *        	Array of words written after the command.
	 */
	public function run(array $params): void;
	/**
	 * Third stage of command processing, cleanup from command execution can be done here.
	 */
	public function cleanup(): void;
	/**
	 * Sets the current command parameters.
	 *
	 * @param array $params
	 *        	Array of words written after the command.
	 */
	public function setParams(array $params);
	/**
	 * Should return the current command parameters.
	 *
	 * @return array Array of words written after the command.
	 */
	public function getParams(): array;
	/**
	 * Sets reference to main bot instance.
	 *
	 * @param Alassea $bot
	 */
	public function setBot(Alassea $bot);
	/**
	 * Gets the reference to main bot instance.
	 *
	 * @return Alassea
	 */
	public function getBot(): Alassea;
	/**
	 * Sets reference to the discord client.
	 *
	 * @param Discord $discord
	 */
	public function setDiscord(Discord $discord);
	/**
	 * Gets the reference to the discord cliet.
	 *
	 * @return Discord
	 */
	public function getDiscord(): Discord;
	/**
	 * Sets the message on which this command was written.
	 *
	 * @param Message $message
	 */
	public function setMessage(Message $message);
	/**
	 * Gets the message object in which this command was written.
	 *
	 * @return Message
	 */
	public function getMessage(): Message;
	/**
	 * Sets reference to main command logger.
	 *
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger);
	/**
	 * Gets the current logger.
	 *
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface;
	/**
	 * Returns the text to be displayed as help text for this command.
	 *
	 * @return string
	 */
	public function getHelpText(): string;
	/**
	 * Wrapper function to send a simple message with the given text.
	 *
	 * @param string $text
	 */
	public function sendMessageSimple(string $text);
	/**
	 * Adds a discord field to the embed passed as argument.
	 *
	 * @param Embed $embed
	 *        	Embed object on which the Field will be added.
	 * @param string $fieldName
	 *        	Field name.
	 * @param string $fieldValue
	 *        	Field value.
	 * @param bool $inline
	 *        	Controls whether the field is displayed inline.
	 */
	public function addField(Embed &$embed, string $fieldName, string $fieldValue, bool $inline);
	/**
	 * Returns the cache context name used by this command.
	 *
	 * @return string Alfa numeric string to represent the context of this store, please only use characters that can be used in filenames.
	 */
	public function getCacheContextName(): string;
	/**
	 * Gets the reference to this command's cache store.
	 *
	 * @return CacheInterface
	 */
	public function getCache(): CacheInterface;
}
