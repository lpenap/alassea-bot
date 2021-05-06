<?php

namespace Alassea\Commands;

use Alassea\Alassea;
use Alassea\Preferences;
use Discord\Parts\Channel\Message;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;

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
			$commandClass = $this->getClassName ( $path, $cmd );
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
		if ($this->bot->getPrefs()->get('reply_on_wrong_command')) {
			$message->reply ( "Oops!, i don't know that command, try using `" .  $this->bot->getPrefs ()->get ( 'prefix' ) . 'help`');
		}
		return false;
	}
	protected function getClassName($path, $cmd) {
		return __NAMESPACE__ . '\\' . $path . '\\' . ucfirst ( $cmd ) . $this->bot->getPrefs ()->get ( 'cmd_suffix' );
	}
	protected function runCommandLifecycle($commandInstance, $commandClass, $path, Message $message, $params) {
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
	public function getCommandsHelp() {
		// TODO restrict help output depending on user permissions (i.e. don't show admin commands to non admin members.
		return $this->buildCommandsHelp ();
	}
	protected function buildCommandsHelp() {
		$this->logger->debug ( "Building commands help!" );
		$result = [ ];
		$paths = $this->bot->getPrefs ()->get ( 'cmd_paths' );
		foreach ( array_reverse ( $paths ) as $path ) {
			$dir = __DIR__ . DIRECTORY_SEPARATOR . $path;
			if (is_dir ( $dir ) && (($files = scandir ( $dir )) !== false)) {
				foreach ( $files as $file ) {
					$filename = pathinfo ( $dir . $file, PATHINFO_FILENAME );
					$cmdSuffix = $this->bot->getPrefs ()->get ( 'cmd_suffix' );
					if (isset ( $filename ) && str_contains ( $filename, $cmdSuffix )) {
						// removing $path from filename, this should not happen but it does.. <shrug>
						$filename = str_replace ( $path, '', $filename );
						$cmd = strtolower ( str_replace ( $cmdSuffix, '', $filename ) );
						$className = __NAMESPACE__ . '\\' . $path . '\\' . $filename;
						if (class_exists ( $className )) {
							$result [$cmd] = (new $className ())->getHelpText ();
						}
					}
				}
			}
		}
		return $result;
	}
	public function isSysadmin($authorId) {
		return in_array ( $authorId, $this->bot->getPrefs ()->get ( 'sysadmins' ) );
	}
}

