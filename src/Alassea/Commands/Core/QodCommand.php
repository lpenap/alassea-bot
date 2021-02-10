<?php

namespace Alassea\Commands\Core;

use Alassea\Commands\AbstractCommand;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Alassea\Database\Cache;

class QodCommand extends AbstractCommand {
	protected $url = null;
	protected $category = null;
	protected const DEFAULT_CATEGORY = 'funny';
	protected const DEFAULT_URL = 'https://quotes.rest/qod.json?category=';
	public function run(array $params): void {
		$text = null;
		$qodResponse = $this->getQod ();
		$embed = null;
		$this->getLogger ()->debug ( "QodCommand: getQod", json_decode ( json_encode ( $qodResponse ), true ) );

		if (isset ( $qodResponse->contents->quotes [0] )) {
			$text = 'Here is your \'' . $qodResponse->contents->quotes [0]->title . '\'';
		} else {
			$text = 'Oops!, the qotd service is down (or the selected category doesn\'t exist)! Nevermind, here is my default quote!:';
			$qodResponse = $this->getDefaultQuote ();
		}
		$quote = $qodResponse->contents->quotes [0];
		$embed = $this->getDiscord ()->factory ( Embed::class, [ 
				"title" => " ",
				"description" => $quote->quote,
				'color' => '#0099ff',
				"thumbnail" => [ 
						"url" => $quote->background,
						"height" => 20,
						"width" => 20
				],
				"author" => [ 
						'name' => $quote->author
				]
		], true );
		if (isset ( $qodResponse->copyright )) {
			$embed->setFooter ( 'They Said So(R), ' . $qodResponse->copyright->url, 'https://theysaidso.com/branding/theysaidso.png' );
		}
		$this->getLogger ()->debug ( "QodCommand: Sending embed", json_decode ( json_encode ( $embed ), true ) );
		$message = $this->getMessage ();
		$message->channel->sendMessage ( "{$message->author}, {$text}", false, $embed )->then ( function (Message $message) {
			$this->getLogger ()->debug ( "QodCommand: QoD sent!" );
		} )->otherwise ( function (\Exception $e) {
			$this->getLogger ()->error ( 'QodCommand: Error sending message: ' . $e->getMessage () );
		} );
	}
	public function prepare(array $params): void {
		if (isset ( $params [0] ) && $params [0] != "") {
			$this->category = strtolower ( $params [0] );
		} else {
			$this->category = QodCommand::DEFAULT_CATEGORY;
		}
		$this->url = QodCommand::DEFAULT_URL . $this->category;
		$this->getLogger ()->debug ( "QodCommand: setting qod url to " . $this->url );
	}
	protected function getQod() {
		$key = date ( "Y-m-d" ) . $this->category;
		return $this->getBot ()->getCache ()->get ( $key, function ($myQod) use ($key) {
			if ($myQod == null) {
				$this->getLogger ()->info ( "QodCommand: Cache miss for key " . $key . "!, Fetching new QoD" );
				$contents = file_get_contents ( $this->url );
				$array = json_decode ( $contents, true );
				if ($array != null) {
					$myQod = $this->getBot ()->getCache ()->insert ( $key, $array, "qod" );
				}
			} else {
				$this->getLogger ()->debug ( "QodCommand: Key " . $key . " found in chache!, returning" );
			}
			return json_decode ( json_encode ( $myQod ) );
		}, "qod" );
	}
	protected function getDefaultQuote($asJsonObj = true) {
		$quote = array (
				'contents' => array (
						'quotes' => array (
								array (
										'title' => 'SciFi quote of the day',
										'quote' => 'The saddest aspect of life right now is that science gathers knowledge faster than society gathers wisdom.',
										'background' => 'https://upload.wikimedia.org/wikipedia/commons/3/34/Isaac.Asimov01.jpg',
										'author' => 'Isaac Asimov',
										'tags' => array (
												'default'
										)
								)
						)
				)
		);
		return $asJsonObj ? json_decode ( json_encode ( $quote ) ) : $quote;
	}
	public function getHelpText(): string {
		return 'Prints quote of the day for the given category from theysaidso.com';
	}
}