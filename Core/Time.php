<?php

class Time {

	private static $_aStartTime = array();

	private static $_aStopTime = array();

	private static $_aTotalTime = array();

	public static function start($sKey = null) {
		if ($sKey) {
			self::$_aStartTime[$sKey] = microtime(true);
		} else {
			self::$_aStartTime[0] = microtime(true);
		}
	}

	public static function stop($sKey = null) {
		if ($sKey) {
			if (!isset(self::$_aStartTime[$sKey])) {
				$aLastMoment = array_keys(self::$_aStopTime);
				self::$_aStartTime[$sKey] = self::$_aStopTime[end($aLastMoment)];
			}
			self::$_aStopTime[$sKey] = microtime(true);
		} else {
			self::$_aStopTime[0] = microtime(true);
		}
	}

	public static function total($bReturnAsMiliseconds = false) {
		if (!isset(self::$_aStopTime[0])) {
			self::$_aStopTime[0] = microtime(true);
		}
		
		foreach (self::$_aStartTime as $tk => $time) {
			if ($bReturnAsMiliseconds) {
				self::$_aTotalTime[$tk] = round((self::$_aStopTime[$tk] - self::$_aStartTime[$tk]) * 1000);
			} else {
				self::$_aTotalTime[$tk] = self::$_aStopTime[$tk] - self::$_aStartTime[$tk];
			}
		}
	}

	public static function stats($iThreshold = 0) {
		if (empty(self::$_aTotalTime)) {
			self::total();
		}
		$aStats = array();
		foreach (self::$_aTotalTime as $tk => $time) {
			if ($time > $iThreshold) {
				$aStats[$tk] = $time;		
			}
		}
		asort($aStats);
		return $aStats;
	}

	public static function get($sKey = null) {
		if (empty(self::$_aTotalTime)) {
			self::total();
		}
		if ($sKey) {
			return self::$_aTotalTime[$sKey];
		} else {
			return self::$_aTotalTime[0];
		}
	}

	public static function show($sKey = null) {
		self::total();
		if ($sKey) {
			// if (!isset(self::$_aTotalTime[$sKey])) {
			// 	self::stop($sKey);
			// }
			echo (int)(self::$_aTotalTime[$sKey] * 1000).'ms';
		} else {
			// if (!isset(self::$_aTotalTime[0])) {
			// 	self::stop();
			// }
			echo (int)(self::$_aTotalTime[0] * 1000).'ms';
		}
	}

	public static function debug() {
		print_r(self::$_aStartTime);
		print_r(self::$_aTotalTime);
	}
}