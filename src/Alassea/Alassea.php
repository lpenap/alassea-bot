<?php

namespace Alassea;

use Discord\Discord;
use Alassea\Database\Cache;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Alassea\Commands\CommandInterface;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Discord\Parts\User\Member;
use Alassea\Events\GuildMemberAddEventHandler;
use Alassea\Events\GuildMemberRemoveEventHandler;
use Alassea\Events\ReadyEventHandler;

class Alassea {
	public const VERSION = "0.6";
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
	protected $commands;
	protected $commandsHelp;
	protected $sysadmins;
	protected const SYSADMIN_CMD_NAMESPACE = 'Alassea\\Commands\\System\\';
	protected const CORE_CMD_NAMESPACE = 'Alassea\\Commands\\Core\\';
	public function __construct($prefs) {
		$this->setPrefs ( $prefs );
		$this->logger = new Monolog ( 'DiscordPHP' );
		$this->logger->pushHandler ( new StreamHandler ( 'php://stdout', $this->logLevel ) );

		$this->cmdPaths = array ();
		$this->cmdPaths [] = Alassea::CUSTOM_CMD_NAMESPACE;
		$this->cmdPaths [] = Alassea::CORE_CMD_NAMESPACE;
		$this->cmdPaths [] = Alassea::SYSADMIN_CMD_NAMESPACE;

		$this->cache = new Cache ( "cache", $this->basedir );
		$this->readCommands ();
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

		if (isset ( $prefs ['log_level'] )) {
			$this->logLevel = $prefs ['log_level'];
		} else {
			$this->logLevel = Monolog::INFO;
		}

		if (isset ( $prefs ['sysadmins'] ) && trim ( $prefs ['sysadmins'] ) != "") {
			$this->sysadmins = explode ( ",", preg_replace ( "/\s+/", "", $prefs ['sysadmins'] ) );
		} else {
			$this->sysadmins = [ ];
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
				'token' => $this->token,
				'loadAllMembers' => true
		] );
		$this->setupEventHandlers ();
		$this->discord->run ();
	}
	protected function setupEventHandlers() {
		// Listen for messages.
		$this->discord->on ( Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
			if ($message->content !== "" && $message->content [0] == $this->prefix) {
				$this->logger->debug ( "Message received: ", [ 
						"author" => $message->author->username,
						"msg" => $message->content
				] );
				$content = preg_replace ( "/\s+/", " ", strtolower ( $message->content ) );
				$params = explode ( " ", $content );
				$cmd = ltrim ( array_shift ( $params ), $this->prefix );
				$this->executeCommand ( $cmd, $params, $discord, $message );
			}
		} );
		$handlers = array (
				'ready' => new ReadyEventHandler (),
				Event::GUILD_MEMBER_ADD => new GuildMemberAddEventHandler (),
				Event::GUILD_MEMBER_REMOVE => new GuildMemberRemoveEventHandler ()
		);
		foreach ( $handlers as $event => $handler ) {
			$handler->setup ( $event, $this->discord, $this );
		}
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
				$commandInstance = null;
				if (in_array ( $cmd, array_keys ( $this->commands ) )) {
					$this->logger->debug ( "Command Found in Memory! Executing: " . $commandClass );
					$commandInstance = $this->commands [$cmd];
				} else if (class_exists ( $commandClass )) {
					$this->logger->debug ( "Command Found in Disk! Executing: " . $commandClass );
					$commandInstance = new $commandClass ();
					$this->addCommandToCache ( $cmd, $commandInstance );
				}
				$this->runCommandLifecycle ( $commandInstance, $path, $discord, $message, $params, $executed );
				if ($commandInstance !== null) {
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
	private function addCommandToCache($cmd, CommandInterface $commandInstance) {
		$this->commands [$cmd] = $commandInstance;
		$this->commandsHelp [$cmd] = $commandInstance->getHelpText ();
	}
	private function runCommandLifecycle($commandInstance, $path, $discord, Message $message, $params, &$executed) {
		if ($path == Alassea::SYSADMIN_CMD_NAMESPACE && ! in_array ( $message->author->id, $this->sysadmins )) {
			$this->logger->debug ( "Author is not a System admin, skipping command execution, id:" . $message->author->id );
			return;
		}
		if ($commandInstance !== null) {
			$commandInstance->setParams ( $params );
			$commandInstance->setBot ( $this );
			$commandInstance->setDiscord ( $discord );
			$commandInstance->setMessage ( $message );
			$commandInstance->setLogger ( $this->logger );
			$commandInstance->prepare ( $params );
			$commandInstance->run ( $params );
			$commandInstance->cleanup ();
			$executed = true;
		}
	}
	private function readCommands() {
		// TODO find a way to read all available commands to save disk IO.
		$this->commands = [ ];
		$this->commandsHelp = [ ];
	}
	public function getCommandsHelp() {
		return $this->commandsHelp;
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
