<?php

namespace Alassea;

use Discord\Discord;

class Alassea {
	public const VERSION = "0.3";
	protected const CUSTOM_CMD_NAMESPACE = 'Alassea\\Commands\\Custom\\';
	protected const CUSTOM_CMD_SUFIX = 'Command';
	protected $restartCount;
	protected $execCommand;
	protected $discord;
	protected $prefix;
	protected $startTime;
	protected $cmdPaths;
	public function __construct($prefs) {
		$this->setPrefs ( $prefs );

		$this->cmdPaths = array ();
		$this->cmdPaths [] = $this::CUSTOM_CMD_NAMESPACE;
		$this->cmdPaths [] = 'Alassea\\Commands\\System\\';

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
			echo "Alassea Bot is ready! Restarted " . $this->restartCount . " times", PHP_EOL;
		} );

		// Listen for messages.
		$bot = $this;
		$this->discord->on ( 'message', function ($message, $discord) use ($bot) {
			if ($message->content !== "" && $message->content [0] == $bot->prefix) {
				echo "{$message->author->username}: {$message->content}", PHP_EOL;
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
			try {
				if (class_exists ( $commandClass )) {
					$commandInstance = new $commandClass ();
					$commandInstance->setParams ( $params );
					$commandInstance->setBot ( $this );
					$commandInstance->setDiscord ( $discord );
					$commandInstance->setMessage ( $message );
					$commandInstance->prepare ( $params );
					$commandInstance->run ( $params );
					$commandInstance->cleanup ();
					$executed = true;
					break;
				}
			} catch ( \Exception $e ) {
				echo 'Error processing command (' . $cmd . '): ' . $e->getMessage () . PHP_EOL;
			}
		}
		if (! $executed) {
			$message->reply ( "Oops!, i don't know that command, (RTFM?)" );
		}
	}
	public function getRestartCount() {
		return $this->restartCount;
	}
	public function getUptime() {
		return $this->startTime;
	}
}
