<?php

namespace Alassea;

use Discord\Discord;
use Alassea\Database\Cache;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

class Alassea {
	public const VERSION = "0.4";
	protected const CUSTOM_CMD_NAMESPACE = 'Alassea\\Commands\\Custom\\';
	protected const CUSTOM_CMD_SUFIX = 'Command';
	protected $restartCount;
	protected $execCommand;
	protected $discord;
	protected $prefix;
	protected $startTime;
	protected $cmdPaths;
	protected $cache;
	protected $basedir;
	protected $logLevel;
	protected $logger;
	public function __construct($prefs) {
		$this->setPrefs ( $prefs );
		$this->logger = new Monolog ( 'DiscordPHP' );
		$this->logger->pushHandler ( new StreamHandler ( 'php://stdout', $this->logLevel ) );

		$this->cmdPaths = array ();
		$this->cmdPaths [] = $this::CUSTOM_CMD_NAMESPACE;
		$this->cmdPaths [] = 'Alassea\\Commands\\System\\';

		$this->cache = new Cache ( "cache", $this->basedir );
		$this->startTime = time ();
	}
	protected function setPrefs($prefs) {
		global $argv;
		$this->restartCount = 1;
		if (isset ( $argv [1] ) && is_numeric ( $argv [1] )) {
			$this->restartCount = $argv [1] + 1;
		}

		if (isset ( $prefs ['exec_command'] )) {
			$this->execCommand = $prefs ['exec_command'];
		} else {
			$this->execCommand = $_SERVER ['_'];
		}

		if (isset ( $prefs ['prefix'] )) {
			$this->prefix = $prefs ['prefix'];
		} else {
			$this->prefix = ',';
		}

		if (isset ( $prefs ['token'] )) {
			$this->token = $prefs ['token'];
		} else {
			$this->token = '';
		}

		if (isset ( $prefs ['basedir'] )) {
			$this->basedir = $prefs ['basedir'];
		} else {
			$this->basedir = __DIR__;
		}

		if (isset ( $prefs ['logLevel'] )) {
			$this->logLevel = $prefs ['logLevel'];
		} else {
			$this->logLevel = Monolog::INFO;
		}
	}
	public function restart() {
		global $argv;
		$args = array ();
		$args [] = $argv [0];
		$args [] = $this->restartCount;
		pcntl_exec ( $this->execCommand, $args );
	}
	public function run() {
		$this->discord = new Discord ( [ 
				'token' => $this->token
		] );

		$this->discord->on ( 'ready', function ($discord) {
			$this->logger->info ( "AlasseaBot is ready! ", [ 
					"times" => $this->restartCount
			] );
		} );

		// Listen for messages.
		$bot = $this;
		$this->discord->on ( 'message', function ($message, $discord) use ($bot) {
			if ($message->content !== "" && $message->content [0] == $bot->prefix) {
				$this->logger->debug ( "Message received: ", [ 
						"author" => $message->author->username,
						"msg" => $message->content
				] );
				$content = preg_replace ( "/\s+/", " ", strtolower ( $message->content ) );
				$params = explode ( " ", $content );
				$cmd = ltrim ( array_shift ( $params ), $bot->prefix );
				$bot->executeCommand ( $cmd, $params, $discord, $message );
			}
		} );
		$this->discord->run ();
	}
	protected function executeCommand($cmd, $params, $discord, $message) {
		$executed = false;
		foreach ( $this->cmdPaths as $path ) {
			$commandClass = $path . ucfirst ( $cmd ) . $this::CUSTOM_CMD_SUFIX;
			$this->logger->debug ( "Searching command: ", [ 
					"cmd" => $commandClass,
					"params" => implode ( ",", $params )
			] );
			try {
				if (class_exists ( $commandClass )) {
					$this->logger->debug ( "Command Found! Executing: " . $commandClass );
					$commandInstance = new $commandClass ();
					$commandInstance->setParams ( $params );
					$commandInstance->setBot ( $this );
					$commandInstance->setDiscord ( $discord );
					$commandInstance->setMessage ( $message );
					$commandInstance->setLogger ( $this->logger );
					$commandInstance->prepare ( $params );
					$commandInstance->run ( $params );
					$commandInstance->cleanup ();
					$executed = true;
					return;
				}
			} catch ( \Exception $e ) {
				$this->logger->error ( 'Error processing command (' . $cmd . '): ' . $e->getMessage () );
			}
		}
		if (! $executed) {
			$this->logger->warning ( "Unknown command " . $cmd );
			$message->reply ( "Oops!, i don't know that command, (RTFM?)" );
		}
	}
	public function getRestartCount() {
		return $this->restartCount;
	}
	public function getUptime() {
		return $this->startTime;
	}
	public function getCache(): Cache {
		return $this->cache;
	}
	public function getBasedir() {
		return $this->basedir;
	}
	public function getLogger(): LoggerInterface {
		return $this->logger;
	}
}
