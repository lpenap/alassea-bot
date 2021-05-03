<?php

namespace Alassea\Commands\Core;

use Alassea\Commands\AbstractCommand;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Field;

class WeatherCommand extends AbstractCommand {
	protected $search = null;
	protected $locations = null;
	protected const DEFAULT_WOEID = '468739'; // 468739 for Buenos Aires, AR
	protected const DEFAULT_URL_SEARCH = 'https://www.metaweather.com/api/location/search/?query='; // + query string
	protected const DEFAULT_URL_LOCATION = 'https://www.metaweather.com/api/location/'; // + woeid
	protected const DEFAULT_SERVICE_NAME = 'MetaWeather.com';
	protected const DEFAULT_SERVICE_LINKBACK = 'https://www.metaweather.com/';
	protected const DEFAULT_SERVICE_ICON = 'https://www.metaweather.com/static/img/weather/png/64/lc.png';
	protected const ENCODED_SPACE = '%20';
	protected const CACHE_KEY = 'metaweather';
	protected const IMG_PREFIX = 'https://www.metaweather.com/static/img/weather/png/64/';
	protected const IMG_SUFIX = '.png';
	protected const TIME_FORMAT = 'Y-m-d \*\*G:i\*\*';
	public function run(array $params): void {
		$text = null;
		$embed = null;
		if (count ( $this->locations ) < 2) {
			/**
			 * Get a JSON example for locations array at:
			 * https://www.metaweather.com/api/location/search/?query=buenos%20aires
			 */
			$woeid = count ( $this->locations ) == 1 ? $this->locations [0] ['woeid'] : self::DEFAULT_WOEID;
			$this->getLogger ()->debug ( "WeatherCommand: fetching single location woeid: " . $woeid );
			$embed = $this->getWeather ( $woeid );
			$text = $embed == null ? "Oops!, the weather service (at " . self::DEFAULT_SERVICE_NAME . ") is down!, please try again later" : "here's the requested weather:";
		} else {
			$this->getLogger ()->debug ( "WeatherCommand: multiple locations found! sending search results" );
			$embed = $this->getDiscord ()->factory ( Embed::class, [ 
					"title" => " ",
					"description" => implode ( ', ', array_column ( $this->locations, 'title' ) ),
					'color' => '#0099ff'
			], true );
			$text = "I found multiple locations matching your request!, please try again:";
		}
		if ($embed != null) {
			$embed->setFooter ( 'Weather from ' . self::DEFAULT_SERVICE_NAME, self::DEFAULT_SERVICE_ICON );
		}
		$message = $this->getMessage ();
		$message->channel->sendMessage ( "{$message->author}, {$text}", false, $embed )->then ( function (Message $message) {
			$this->getLogger ()->debug ( "WeatherCommand: weather sent!" );
		} )->otherwise ( function (\Exception $e) {
			$this->getLogger ()->error ( 'WeatherCommand: Error sending message: ' . $e->getMessage () );
		} );
	}
	public function prepare(array $params): void {
		$this->locations = array ();
		if (isset ( $params [0] ) && $params [0] != "") {
			$searchLocation = strtolower ( implode ( self::ENCODED_SPACE, $params ) );
			// get location from the cache or search for it
			$this->getBot ()->getCache ()->get ( $searchLocation, function ($myLocations) use ($searchLocation) {
				if ($myLocations == null) {
					$this->getLogger ()->debug ( "WeatherCommand: Cache miss for location '" . $searchLocation . "'!, making a new search" );
					$locations = json_decode ( file_get_contents ( self::DEFAULT_URL_SEARCH . $searchLocation ), true );
					if ($locations != null) {
						$this->getBot ()->getCache ()->insert ( $searchLocation, $locations, self::CACHE_KEY );
						$this->locations = $locations;
					}
				} else {
					$this->getLogger ()->debug ( "WeatherCommand: search '" . $searchLocation . "' found in chache!, returning" );
					$this->locations = $myLocations;
				}
			}, self::CACHE_KEY );
		}
	}
	protected function getWeather($woeid) {
		$weather = $this->getBot ()->getCache ()->getForToday ( '__woeid-' . $woeid, function ($data) use ($woeid) {
			if ($data == null) {
				$this->getLogger ()->debug ( "WeatherCommand: Cache miss for woeid '" . $woeid . "'!, fetching new data" );
				$weather = json_decode ( file_get_contents ( self::DEFAULT_URL_LOCATION . $woeid ), true );
				if ($weather != null) {
					$data = $this->getBot ()->getCache ()->insert ( '__woeid-' . $woeid, $weather, self::CACHE_KEY );
				}
			} else {
				$this->getLogger ()->debug ( "WeatherCommand: woeid '" . $woeid . "' found in chache!, returning" );
			}
			return $data;
		}, true, self::CACHE_KEY );
		$embed = null;
		if ($weather != null) {
			/**
			 * Get a JSON example for weather data at:
			 * https://www.metaweather.com/api/location/468739/
			 */
			$cur = $weather ['consolidated_weather'] [0];
			$title = 'Weather in %s, %s';
			$description = '** %s ** with ** %d%% ** humidity and ** %d km/h** winds.';
			$embed = $this->getDiscord ()->factory ( Embed::class, [ 
					"title" => sprintf ( $title, $weather ['title'], $weather ['parent'] ['title'] ),
					"description" => sprintf ( $description, $cur ['weather_state_name'], $cur ['humidity'], round ( $cur ['wind_speed'] * 1.609, 2 ) ),
					'color' => '#0099ff',
					"thumbnail" => [ 
							"url" => self::IMG_PREFIX . $cur ['weather_state_abbr'] . self::IMG_SUFIX,
							"height" => 20,
							"width" => 20
					]
			], true );
			$t = round ( $cur ['min_temp'], 1 );
			$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
					"name" => ":thermometer: Temp",
					"value" => $t . "°C " . $this->tempToEmoji ( $t ),
					"inline" => true
			] ) );
			$t = round ( $cur ['min_temp'], 1 );
			$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
					"name" => "Min",
					"value" => $t . "°C " . $this->tempToEmoji ( $t ),
					"inline" => true
			] ) );
			$t = round ( $cur ['max_temp'], 1 );
			$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
					"name" => "Max",
					"value" => $t . "°C " . $this->tempToEmoji ( $t ),
					"inline" => true
			] ) );
			$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
					"name" => "Humidity",
					"value" => $cur ['humidity'] . '%',
					"inline" => true
			] ) );
			$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
					"name" => "Visibility",
					"value" => round ( $cur ['visibility'] * 1.609, 1 ) . ' Km',
					"inline" => true
			] ) );
			$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
					"name" => "Confidence",
					"value" => $cur ['predictability'] . '%',
					"inline" => true
			] ) );
			$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
					"name" => "Timezone",
					"value" => $weather ['timezone'],
					"inline" => false
			] ) );
			$t = (new \DateTime ( "now", new \DateTimeZone ( $weather ['timezone'] ) ))->getTimestamp ();
			$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
					"name" => $this->timeToEmoji ( $t ) . ' ' . $weather ['timezone_name'] . " Time",
					"value" => date ( self::TIME_FORMAT, $t ),
					"inline" => true
			] ) );
			$t = strtotime ( $weather ['sun_rise'] );
			$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
					"name" => $this->timeToEmoji ( $t ) . ' ' . "Sunrise",
					"value" => date ( self::TIME_FORMAT, $t ),
					"inline" => true
			] ) );
			$t = strtotime ( $weather ['sun_set'] );
			$embed->addField ( $this->getDiscord ()->factory ( Field::class, [ 
					"name" => $this->timeToEmoji ( $t ) . ' ' . "Sunset",
					"value" => date ( self::TIME_FORMAT, $t ),
					"inline" => true
			] ) );
		}
		return $embed;
	}
	protected function tempToEmoji($temp): string {
		return $temp < 10 ? ":cold_face:" : ($temp > 30 ? ":hot_face:" : "");
	}
	protected function timeToEmoji($timestamp): string {
		$h = date ( 'g', $timestamp );
		$m = date ( 'i', $timestamp );
		if ($m < 15) {
			$m = "";
		} else if ($m > 15 && $m < 45) {
			$m = "30";
		} else {
			$m = "";
			$h = $h ++ % 12;
		}
		return sprintf ( ':clock%s%s:', $h, $m );
	}
	public function getHelpText(): string {
		return 'Prints weather information for a given location from ' . self::DEFAULT_SERVICE_NAME;
	}
}