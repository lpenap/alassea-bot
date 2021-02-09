<?php

namespace Alassea\Commands;

use Psr\Log\LoggerInterface;

abstract class AbstractCommand implements CommandInterface {
	protected $discord;
	protected $message;
	protected $params;
	protected $bot;
	protected $logger;
	public function prepare($params) {
	}
	public function cleanup() {
	}
	public function setBot($bot) {
		$this->bot = $bot;
	}
	public function setDiscord($discord) {
		$this->discord = $discord;
	}
	public function getBot() {
		return $this->bot;
	}
	public function getMessage() {
		return $this->message;
	}
	public function getDiscord() {
		return $this->discord;
	}
	public function setMessage($message) {
		$this->message = $message;
	}
	public function getParams() {
		return $this->params;
	}
	public function setParams($params) {
		$this->params = $params;
	}
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}
	public function getLogger(): LoggerInterface {
		return $this->logger;
	}
}