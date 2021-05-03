<?php

namespace Alassea\Utils;

class DateTimeUtils {
	public static function getTimeAgo($time) {
		$estimate_time = time () - $time;

		if ($estimate_time < 1) {
			return 'less than 1 second ago';
		}

		$condition = array (
				12 * 30 * 24 * 60 * 60 => 'year',
				30 * 24 * 60 * 60 => 'month',
				24 * 60 * 60 => 'day',
				60 * 60 => 'hour',
				60 => 'minute',
				1 => 'second'
		);

		foreach ( $condition as $secs => $str ) {
			$d = $estimate_time / $secs;

			if ($d >= 1) {
				$r = round ( $d );
				return 'about ' . $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
			}
		}
	}
	public static function timeToEmoji($timestamp): string {
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
}