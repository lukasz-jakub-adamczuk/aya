<?php

class Time {

	private static $_aStartTime = array();

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
			self::$_aTotalTime[$sKey] = microtime(true) - self::$_aStartTime[$sKey];
		} else {
			self::$_aTotalTime[0] = microtime(true) - self::$_aStartTime[0];
		}
	}

	public static function show($sKey = null) {
		if ($sKey) {
			if (!isset(self::$_aTotalTime[$sKey])) {
				self::stop($sKey);
			}
			echo (int)(self::$_aTotalTime[$sKey] * 1000).'ms';
		} else {
			if (!isset(self::$_aTotalTime[0])) {
				self::stop();
			}
			echo (int)(self::$_aTotalTime[0] * 1000).'ms';
		}
	}

	public static function debug() {
		print_r(self::$_aStartTime);
		print_r(self::$_aTotalTime);
	}
}