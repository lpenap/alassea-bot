<?php

namespace Alassea\Commands\System;

use Alassea\Commands\AbstractCommand;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;

class QodCommand extends AbstractCommand {
	protected $url = 'https://quotes.rest/qod.json?category=';
	protected $default = 'funny';
	public function run($params) {
		$text = null;
		$qodResponse = $this->getQod ();
		$embed = null;
		if (isset ( $qodResponse->contents->quotes [0] )) {
			$quote = $qodResponse->contents->quotes [0];
			$text = 'Here is your \'' . $quote->title . '\'';
			$embed = $this->getDiscord ()->factory ( Embed::class, [ 
					"title" => $quote->quote,
					"description" => 'Tags: ' . implode ( ", ", $quote->tags ),
					'color' => '#0099ff',
					"thumbnail" => [ 
							"url" => $quote->background,
							"height" => 20,
							"width" => 20
					],
					"author" => [ 
							'name' => $quote->author
					],
					"footer" => [ 
							'text' => 'They Said So(R), ' . $qodResponse->copyright->url,
							'icon_url' => 'https://theysaidso.com/branding/theysaidso.png'
					]
			], true );
		} else {
			$text = 'Oops!, the qotd service is down (or the selected category doesn\'t exist)!';
		}
		$message = $this->getMessage ();
		$message->channel->sendMessage ( "{$message->author}, {$text}", false, $embed )->then ( function (Message $message) {
			echo 'Message sent!' . PHP_EOL;
		} )->otherwise ( function (\Exception $e) {
			echo 'Error sending message: ' . $e->getMessage () . PHP_EOL;
		} );
	}
	public function prepare($params) {
		if (isset ( $params [0] )) {
			$this->url .= strtolower ( $params [0] );
		} else {
			$this->url .= $this->default;
		}
	}
	public function getQod() {
		return json_decode ( file_get_contents ( $this->url ) );
	}
}