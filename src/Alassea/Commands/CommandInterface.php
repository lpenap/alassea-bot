<?php

namespace Alassea\Commands;

use Psr\Log\LoggerInterface;

interface CommandInterface {
	public function prepare($params);
	public function run($params);
	public function cleanup();
	public function setParams($params);
	public function getParams();
	public function setBot($bot);
	public function getBot();
	public function setDiscord($discord);
	public function getDiscord();
	public function setMessage($message);
	public function getMessage();
	public function setLogger(LoggerInterface $logger);
	public function getLogger(): LoggerInterface;
	public function getHelpText();
}
