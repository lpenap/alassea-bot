<?php

namespace Alassea;

use Monolog\Logger as Monolog;

class Preferences {
	public const VERSION = "0.7";
	// Namespaces must be relative to Alassea\\Commands root namespace
	// which is where the CommandManager is located.
	// Also, do not include the trailing \\ in the relative namespace.
	public const CUSTOM_CMD_NAMESPACE = 'Custom';
	public const CORE_CMD_NAMESPACE = 'Core';
	public const GUILDADMIN_CMD_NAMESPACE = 'GuildAdmin';
	public const SYSADMIN_CMD_NAMESPACE = 'System';
	public const CUSTOM_CMD_SUFIX = 'Command';
	protected $prefs;
	function __construct() {
		$this->setDefaults ();
	}
	protected function setDefaults() {
		$this->prefs = array (
				'exec_command' => $_SERVER ['_'],
				'prefix' => ',',
				'token' => '',
				'basedir' => __DIR__,
				'log_level' => Monolog::INFO,
				'load_all_members' => true,
				'sysadmins' => [ ],
				'cmd_paths' => [ 
						self::CUSTOM_CMD_NAMESPACE,
						self::CORE_CMD_NAMESPACE,
						self::GUILDADMIN_CMD_NAMESPACE,
						self::SYSADMIN_CMD_NAMESPACE
				],
				'cmd_suffix' => self::CUSTOM_CMD_SUFIX
		);
	}
	public function setAll(array $prefs) {
		foreach ( $prefs as $key => $val ) {
			$this->prefs [$key] = $val;
		}
		if (isset ( $prefs ['sysadmins'] ) && trim ( $prefs ['sysadmins'] ) != "") {
			$this->prefs ['sysadmins'] = explode ( ",", preg_replace ( "/\s+/", "", $prefs ['sysadmins'] ) );
		} else {
			$this->prefs ['sysadmins'] = [ ];
		}
	}
	public function get($key) {
		return isset ( $this->prefs [$key] ) ? $this->prefs [$key] : null;
	}
	public function set($key, $value) {
		if (isset ( $key ) && $key != null && $key != '') {
			$this->prefs [$key] = $value;
		}
	}
}

