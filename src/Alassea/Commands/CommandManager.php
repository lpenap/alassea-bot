<?php

namespace Alassea\Commands;

use Alassea\Alassea;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;
use Discord\Parts\Channel\Message;
use Alassea\Preferences;

class CommandManager {
	private static $instances = [ ];
	protected $bot;
	protected $logger;
	protected function __construct() {
	}
	protected function __clone() {
	}
	public function __wakeup() {
		throw new \Exception ( "Cannot unserialize a singleton." );
	}
	public static function instance(): CommandManager {
		$cls = static::class;
		if (! isset ( self::$instances [$cls] )) {
			self::$instances [$cls] = new static ();
		}

		return self::$instances [$cls];
	}
	public function setBot(Alassea $bot) {
		$this->bot = $bot;
		$this->logger = new Monolog ( 'CommandManager' );
		$this->logger->pushHandler ( new StreamHandler ( 'php://stdout', $this->bot->getPrefs ()->get ( 'log_level' ) ) );
	}
	public function getBot(): Alassea {
		return $this->bot;
	}
	public function executeCommand($cmd, $params, $message) {
		$paths = $this->bot->getPrefs ()->get ( 'cmd_paths' );
		foreach ( $paths as $path ) {
			$commandClass = $path . ucfirst ( $cmd ) . $this->bot->getPrefs ()->get ( 'cmd_suffix' );
			$this->logger->debug ( "Searching command: ", [ 
					"cmd" => $commandClass,
					"params" => implode ( ",", $params )
			] );
			try {
				if (class_exists ( $commandClass )) {
					$this->logger->debug ( "Command Found! Executing: " . $commandClass );
					$commandInstance = new $commandClass ();
					return $this->runCommandLifecycle ( $commandInstance, $commandClass, $path, $message, $params );
				}
			} catch ( \Exception $e ) {
				$this->logger->error ( 'Error processing command (' . $cmd . '): ' . $e->getMessage () );
			}
		}
		$this->logger->debug ( "Unknown command " . $cmd );
		$message->reply ( "Oops!, i don't know that command, (RTFM?)" );
		return false;
	}
	private function runCommandLifecycle($commandInstance, $commandClass, $path, Message $message, $params) {
		if ($path == Preferences::SYSADMIN_CMD_NAMESPACE && ! $this->isSysadmin ( $message->author->id )) {
			$this->logger->debug ( "Author is not a System admin, skipping command execution, id:" . $message->author->id );
			return false;
		}
		if ($commandInstance !== null) {
			$commandInstance->setParams ( $params );
			$commandInstance->setBot ( $this->bot );
			$commandInstance->setDiscord ( $this->bot->getDiscord () );
			$commandInstance->setMessage ( $message );
			$cmdLogger = new Monolog ( $commandClass );
			$cmdLogger->pushHandler ( new StreamHandler ( 'php://stdout', $this->bot->getPrefs ()->get ( 'log_level' ) ) );
			$commandInstance->setLogger ( $cmdLogger );
			$commandInstance->prepare ( $params );
			$commandInstance->run ( $params );
			$commandInstance->cleanup ();
			return true;
		}
		return false;
	}
	public function isSysadmin($authorId) {
		return in_array ( $authorId, $this->bot->getPrefs ()->get ( 'sysadmins' ) );
	}
}

