<?php
include __DIR__ . '/../vendor/autoload.php';

use Alassea\Alassea;
use Monolog\Logger as Monolog;
function launch() {
	$discordToken = getenv ( 'ALASSEA_DISCORD_TOKEN', true );
	if ($discordToken === false) {
		die ( "Please set ALASSEA_DISCORD_TOKEN environment variable" );
	}
	$bot = new Alassea ( [ 
			'exec_command' => $_SERVER ['_'], // defaults to $_SERVER['_']
			'prefix' => ',', // defaults to ,
			'basedir' => __DIR__, // defaults to __DIR__ location of Alassea class
			'token' => $discordToken,
			'log_level' => Monolog::DEBUG, // defaults to INFO
			'sysadmins' => getenv ( 'ALASSEA_DISCORD_SYSADMINS' ) // comma separated string with sysadmin ids, defaults to no admin
	] );
	$bot->run ();
}

launch ();