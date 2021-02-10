<?php

namespace Alassea\Commands;

use Psr\Log\LoggerInterface;
use Alassea\Alassea;
use Discord\Discord;
use Discord\Parts\Channel\Message;

interface CommandInterface {
	public function prepare(array $params): void;
	public function run(array $params): void;
	public function cleanup(): void;
	public function setParams(array $params);
	public function getParams(): array;
	public function setBot(Alassea $bot);
	public function getBot(): Alassea;
	public function setDiscord(Discord $discord);
	public function getDiscord(): Discord;
	public function setMessage(Message $message);
	public function getMessage(): Message;
	public function setLogger(LoggerInterface $logger);
	public function getLogger(): LoggerInterface;
	public function getHelpText(): string;
}
