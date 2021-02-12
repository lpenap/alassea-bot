<?php

namespace Alassea;

use Monolog\Logger as Monolog;

class Preferences {
	public const VERSION = "0.6";
	public const CUSTOM_CMD_NAMESPACE = 'Alassea\\Commands\\Custom\\';
	public const CORE_CMD_NAMESPACE = 'Alassea\\Commands\\Core\\';
	public const GUILDADMIN_CMD_NAMESPACE = 'Alassea\\Commands\\GuildAdmin\\';
	public const SYSADMIN_CMD_NAMESPACE = 'Alassea\\Commands\\System\\';
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
			$this->sysadmins = explode ( ",", preg_replace ( "/\s+/", "", $prefs ['sysadmins'] ) );
		} else {
			$this->sysadmins = [ ];
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

