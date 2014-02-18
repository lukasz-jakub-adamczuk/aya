<?php

class Debug {

	private static $_aLogs = array();

	public static function log($sFilename, $aData) {
		
	}

	public static function show($mVariable, $sName = null) {
		$aBacktrace = debug_backtrace();

		if (is_array($mVariable)) {
			// echo $aBacktrace[0]['file'].' '.$aBacktrace[0]['line']."\n";
			// print_r($mVariable);
			// echo "\n";
		} else {
			// echo $aBacktrace[0]['file'].' '.$aBacktrace[0]['line'].' '.$mVariable;
		}
		$aBacktrace[0]['var'] = print_r($mVariable, true);
		$aFileParts = explode('/', $aBacktrace[0]['file']);
		$aBacktrace[0]['file_short'] = end($aFileParts);
		if ($sName) {
			$aBacktrace[0]['name'] = $sName;
		}
		self::$_aLogs[] = $aBacktrace[0];
	}

	public static function formatLog() {

	}

	public static function getLogs() {
		if (DEBUG_MODE) {
			return self::$_aLogs;
		}
	}

	public static function showLogs() {
		print_r(self::$_aLogs);
	}
}