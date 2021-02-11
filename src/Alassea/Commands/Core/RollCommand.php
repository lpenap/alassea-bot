<?php

namespace Alassea\Commands\Core;

use Alassea\Commands\AbstractCommand;
use DiceBag\DiceBag;

class RollCommand extends AbstractCommand {
	public function run(array $params): void {
		// $randomizationEngine = new DiceBag\Randomization\MersenneTwister\MersenneTwister ();
		// $diceBag = DiceBag::factory ( implode ( ' ', $params ), $randomizationEngine );
		try {
			$diceStr = implode ( ' ', $params );
			$this->basicValidate ( $diceStr );
			$diceBag = DiceBag::factory ( $diceStr );
			$this->sendMessageSimple ( $this->getMessage ()->author->username . " Roll: `" . $diceBag . "`" );
		} catch ( \Exception $e ) {
			$this->sendMessageSimple ( $this->getMessage ()->author->username . ", please check your roll syntax and try again!" );
		}
	}
	public function getHelpText(): string {
		return "Rolls a dice pool using standard dice notation";
	}
	protected function basicValidate($str) {
		// DiceBag breaks the bot if certain patterns are used
		// TODO find more breaking patterns and place them here
		$this->getLogger ()->debug ( "RollCommand: Validating roll syntax: " . $str );
		$patterns = array (
				'/.*\+!.*/'
		);
		foreach ( $patterns as $pattern ) {
			$match = preg_match ( $pattern, $str );
			$this->getLogger ()->debug ( "RollCommand: Roll syntax check: " . $pattern . " = " . $match );
			if ($match === false || $match == 1) {
				throw new \Exception ( 'Wrong roll syntax' );
			}
		}
	}
}