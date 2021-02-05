<?php

namespace Alassea\Commands\System;

use Alassea\Commands\AbstractCommand;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Alassea\Database\Cache;

class QodCommand extends AbstractCommand {
	protected $url = 'https://quotes.rest/qod.json?category=';
	protected $defaultCategory = 'funny';
	protected $category = null;
	public function run($params) {
		$text = null;
		$qodResponse = $this->getQod ();
		$embed = null;

		if (isset ( $qodResponse->contents->quotes [0] )) {
			$text = 'Here is your \'' . $qodResponse->contents->quotes [0]->title . '\'';
		} else {
			$text = 'Oops!, the qotd service is down (or the selected category doesn\'t exist)! Nevermind, here is my default quote!:';
			$qodResponse = $this->getDefaultQuote ();
		}
		$quote = $qodResponse->contents->quotes [0];
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
				]
		], true );
		if (isset ( $qodResponse->copyright )) {
			$embed->setFooter ( 'They Said So(R), ' . $qodResponse->copyright->url, 'https://theysaidso.com/branding/theysaidso.png' );
		}
		$message = $this->getMessage ();
		$message->channel->sendMessage ( "{$message->author}, {$text}", false, $embed )->then ( function (Message $message) {
			echo '\nMessage sent!' . PHP_EOL;
		} )->otherwise ( function (\Exception $e) {
			echo '\nError sending message: ' . $e->getMessage () . PHP_EOL;
		} );
	}
	public function prepare($params) {
		if (isset ( $params [0] )) {
			$this->category = strtolower ( $params [0] );
			$this->url .= $this->category;
		} else {
			$this->url .= $this->defaultCategory;
			$this->category = $this->defaultCategory;
		}
	}
	public function getQod() {
		$key = date ( "Y-m-d" ) . $this->category;
		return $this->getBot ()->getCache ()->get ( $key, function ($myQod) use ($key) {
			if ($myQod == null) {
				echo "Fetching new QoD";
				$contents = file_get_contents ( $this->url );
				$array = json_decode ( $contents, true );
				if ($array != null) {
					$myQod = $this->getBot ()->getCache ()->insert ( $key, $array, "qod" );
				}
			} else {
				echo "Found QoD in chache!, returning";
			}
			return json_decode ( json_encode ( $myQod ) );
		}, "qod" );
	}
	private function getDefaultQuote($asJsonObj = true) {
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
}