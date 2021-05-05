<?php

namespace Alassea\Commands\Core;

use Alassea\Commands\AbstractCommand;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;

class RaeCommand extends AbstractCommand {
	protected $definition = null;
	protected $word = null;
	protected $error = false;
	protected const DEFAULT_URL = 'https://dle.rae.es/';
	protected const DEFAULT_SERVICE_NAME = 'Diccionario de la lengua española RAE';
	protected const DEFAULT_SERVICE_LINKBACK = 'https://dle.rae.es/';
	protected const DEFAULT_SERVICE_ICON = 'https://dle.rae.es/app/doc/es/img/dle.jpg';
	protected const CACHE_TTL = 94608000; // 94608000 seconds equals 3 years
	protected const SEARCH_TAG_BEGIN = '<script type="application/ld+json">';
	protected const SEARCH_TAG_END = '</script>';
	protected const TYPE_KEY = '@type';
	protected const TYPE_VALUE = 'DefinedTerm';
	protected const DESCRIPTION_KEY = 'description';
	public function run(array $params): void {
		$text = null;
		$embed = null;
		if ($this->definition == null) {
			$text = "I couldn't find the definition for '" . $this->word . "'.";
		}
		if ($this->word == null) {
			$text = "Hmm, forgot to write a word?";
		}
		if ($this->error) {
			$text = "Oops!, the RAE service (at '" . self::DEFAULT_SERVICE_NAME . "') is down!, please try again later";
		} else {
			if ($this->definition != null && $this->word != null) {
				$text = "here is the requested definition:";
				$embed = $this->getDiscord ()->factory ( Embed::class, [ 
						"title" => $this->word,
						"description" => $this->definition,
						'color' => '#0099ff',
						"thumbnail" => [ 
								"url" => self::DEFAULT_SERVICE_ICON,
								"height" => 20,
								"width" => 20
						]
				], true );
				$embed->setFooter ( 'Definition from ' . self::DEFAULT_SERVICE_NAME . ', ' . self::DEFAULT_SERVICE_LINKBACK, self::DEFAULT_SERVICE_ICON );
			}
		}

		$message = $this->getMessage ();
		$message->channel->sendMessage ( "{$message->author}, {$text}", false, $embed )->then ( function (Message $message) {
			$this->getLogger ()->debug ( "RaeCommand: Definition sent!" );
		} )->otherwise ( function (\Exception $e) {
			$this->getLogger ()->error ( 'RaeCommand: Error sending message: ' . $e->getMessage () );
		} );
	}
	/**
	 * Gets a word definition from cache or fetchs a new one and cache it.
	 *
	 * {@inheritdoc}
	 * @see \Alassea\Commands\AbstractCommand::prepare()
	 */
	public function prepare(array $params): void {
		if (isset ( $params [0] ) && $params [0] != "") {
			// only take care of first word.
			$word = $params [0];
			if ($word != "") {
				$this->word = $word;
				$this->getCache ()->getWithCallback ( $word, function ($myDefArray) use ($word) {
					if ($myDefArray == null) {
						$this->definition = $this->fetchRaeDefinition ( $word );
						if ($this->definition != null) {
							$this->getCache ()->insertWithTtl ( $word, array (
									$this->definition
							), self::CACHE_TTL );
						}
					} else {
						$this->getLogger ()->debug ( "RaeCommand: word '" . $word . "' found in chache!, returning" );
						$this->definition = $myDefArray [0];
					}
				} );
			}
		}
	}
	protected function getUserAgent() {
		return 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.    93 Safari/537.36';
	}
	protected function fetchRaeDefinition($word) {
		$definition = null;
		$this->getLogger ()->debug ( "RaeCommand: Fetching definition for '" . $word . "'" );
		$context = stream_context_create ( array (
				'http' => array (
						'user_agent' => $this->getUserAgent ()
				)
		) );
		$page = file_get_contents ( self::DEFAULT_URL . $word, false, $context );
		$this->error = $page === false;
		$startsAt = strpos ( $page, self::SEARCH_TAG_BEGIN ) + strlen ( self::SEARCH_TAG_BEGIN );
		$endsAt = strpos ( $page, self::SEARCH_TAG_END, $startsAt );
		$json = substr ( $page, $startsAt, $endsAt - $startsAt );
		$this->getLogger ()->debug ( "RaeCommand: JSON fetched: " . $json );
		$defArray = json_decode ( $json, true );
		/**
		 * Searching for:
		 *
		 * [2] => Array
		 * (
		 * [@type] => DefinedTerm
		 * [@id] => https://dle.rae.es/word
		 * [name] => word
		 * [description] => 1. m. definition one. 2. m. definition two.
		 * [inDefinedTermSet] => https://dle.rae.es/
		 * )
		 */
		foreach ( $defArray as $element ) {
			if (isset ( $element [self::TYPE_KEY] ) && $element [self::TYPE_KEY] == self::TYPE_VALUE) {
				$definition = $element [self::DESCRIPTION_KEY];
				break;
			}
		}
		return $definition;
	}
	public function getHelpText(): string {
		return 'Prints dictionary definition from \'' . self::DEFAULT_SERVICE_NAME . '\'';
	}
}