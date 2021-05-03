<?php

namespace Alassea\Commands\Core;

use Alassea\Commands\AbstractCommand;
use Alassea\Utils\DateTimeUtils;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;

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
	protected const IMG_PREFIX = 'https://www.metaweather.com/static/img/weather/png/64/';
	protected const IMG_SUFIX = '.png';
	protected const TIME_FORMAT = 'Y-m-d \*\*G:i\*\*';
	protected const CACHE_TTL = 300; // 300 seconds = 5min
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
			$this->getCache ()->getWithCallback ( '__location-' . $searchLocation, function ($myLocations) use ($searchLocation) {
				if ($myLocations == null) {
					$this->getLogger ()->debug ( "WeatherCommand: Cache miss for location '" . $searchLocation . "'!, making a new search" );
					$locations = json_decode ( file_get_contents ( self::DEFAULT_URL_SEARCH . $searchLocation ), true );
					if ($locations != null) {
						$this->getCache ()->insert ( '__location-' . $searchLocation, $locations );
						$this->locations = $locations;
					}
				} else {
					$this->getLogger ()->debug ( "WeatherCommand: search '" . $searchLocation . "' found in chache!, returning" );
					$this->locations = $myLocations;
				}
			} );
		}
	}
	protected function getWeather($woeid) {
		$weather = $this->getCache ()->getWithCallback ( '__woeid-' . $woeid, function ($data) use ($woeid) {
			if ($data == null) {
				$this->getLogger ()->debug ( "WeatherCommand: Cache miss for woeid '" . $woeid . "'!, fetching new data" );
				$weather = json_decode ( file_get_contents ( self::DEFAULT_URL_LOCATION . $woeid ), true );
				if ($weather != null) {
					$data = $this->getCache ()->insertWithTtl ( '__woeid-' . $woeid, $weather, self::CACHE_TTL );
				}
			} else {
				$this->getLogger ()->debug ( "WeatherCommand: woeid '" . $woeid . "' found in chache!, returning" );
			}
			return $data;
		} );
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
			$this->addField ( $embed, ":thermometer: Temp", $t . "°C " . $this->tempToEmoji ( $t ), true );
			$t = round ( $cur ['min_temp'], 1 );
			$this->addField ( $embed, "Min", $t . "°C " . $this->tempToEmoji ( $t ), true );
			$t = round ( $cur ['max_temp'], 1 );
			$this->addField ( $embed, "Max", $t . "°C " . $this->tempToEmoji ( $t ), true );
			$this->addField ( $embed, "Humidity", $cur ['humidity'] . '%', true );
			$this->addField ( $embed, "Visibility", round ( $cur ['visibility'] * 1.609, 1 ) . ' Km', true );
			$this->addField ( $embed, "Confidence", $cur ['predictability'] . '%', true );
			$this->addField ( $embed, "Timezone", $weather ['timezone'], false );
			$t = new \DateTime ( "now", new \DateTimeZone ( $weather ['timezone'] ) );
			$this->addField ( $embed, DateTimeUtils::timeToEmoji ( $t->getTimestamp () ) . ' ' . $weather ['timezone_name'] . " Time", $t->format ( self::TIME_FORMAT ), true );
			$t = new \DateTime ( $weather ['sun_rise'], new \DateTimeZone ( $weather ['timezone'] ) );
			$this->addField ( $embed, DateTimeUtils::timeToEmoji ( $t->getTimestamp () ) . ' ' . "Sunrise", $t->format ( self::TIME_FORMAT ), true );
			$t = new \DateTime ( $weather ['sun_set'], new \DateTimeZone ( $weather ['timezone'] ) );
			$this->addField ( $embed, DateTimeUtils::timeToEmoji ( $t->getTimestamp () ) . ' ' . "Sunset", $t->format ( self::TIME_FORMAT ), true );
		}
		return $embed;
	}
	protected function tempToEmoji($temp): string {
		return $temp < 10 ? ":cold_face:" : ($temp > 30 ? ":hot_face:" : "");
	}
	public function getHelpText(): string {
		return 'Prints weather information for a given location from ' . self::DEFAULT_SERVICE_NAME . ' (cached for ' . self::CACHE_TTL . ' seconds)';
	}
}