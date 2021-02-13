<?php

namespace Alassea;

use Alassea\Commands\CommandManager;
use Alassea\Database\Cache;
use Alassea\Events\GuildMemberAddEventHandler;
use Alassea\Events\GuildMemberRemoveEventHandler;
use Alassea\Events\MessageCreateEventHandler;
use Alassea\Events\ReadyEventHandler;
use Discord\Discord;
use Discord\WebSockets\Event;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;

class Alassea {
	protected $discord;
	protected $cache;
	protected $startTime;
	protected $logger;
	protected $restartCount;
	protected $prefs;
	protected $commandManager;
	public function __construct($prefs) {
		$this->setPrefs ( $prefs );
		$this->logger = new Monolog ( 'AlasseaBot' );
		$this->logger->pushHandler ( new StreamHandler ( 'php://stdout', $this->prefs->get ( 'log_level' ) ) );
		$this->cache = new Cache ( "cache", $this->prefs->get ( 'basedir' ) );
		$this->startTime = time ();
		$this->setupCommandManager ();
	}
	protected function setPrefs($prefs) {
		global $argv;
		$this->restartCount = 1;
		if (isset ( $argv [1] ) && is_numeric ( $argv [1] )) {
			$this->restartCount = $argv [1] + 1;
		}
		$this->prefs = new Preferences ();
		$this->prefs->setAll ( $prefs );
	}
	public function getPrefs(): Preferences {
		return $this->prefs;
	}
	public function restart() {
		global $argv;
		$args = array ();
		$args [] = $argv [0];
		$args [] = $this->restartCount;
		pcntl_exec ( $this->prefs->get ( 'exec_command' ), $args );
	}
	public function run() {
		$this->discord = new Discord ( [ 
				'token' => $this->prefs->get ( 'token' ),
				'loadAllMembers' => $this->prefs->get ( 'load_all_members' )
		] );
		$this->setupEventHandlers ();
		$this->discord->run ();
	}
	protected function setupEventHandlers() {
		$handlers = array (
				'ready' => new ReadyEventHandler (),
				Event::MESSAGE_CREATE => new MessageCreateEventHandler (),
				Event::GUILD_MEMBER_ADD => new GuildMemberAddEventHandler (),
				Event::GUILD_MEMBER_REMOVE => new GuildMemberRemoveEventHandler ()
		);
		foreach ( $handlers as $event => $handler ) {
			$handler->setup ( $event, $this->discord, $this );
		}
	}
	protected function setupCommandManager() {
		$this->commandManager = CommandManager::instance ();
		$this->commandManager->setBot ( $this );
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
	public function getDiscord() {
		return $this->discord;
	}
}

