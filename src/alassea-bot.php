<?php
include __DIR__ . '/../vendor/autoload.php';

use Alassea\Alassea;
function launch() {
	$discordToken = getenv ( 'ALASSEA_DISCORD_TOKEN', true );
	if ($discordToken === false) {
		die ( "Please set ALASSEA_DISCORD_TOKEN environment variable" );
	}
	$bot = new Alassea ( [ 
			'exec_command' => $_SERVER ['_'], // defaults to $_SERVER['_']
			'prefix' => ',', // defaults to ,
			'token' => $discordToken
	] );
	$bot->run ();
}

launch ();