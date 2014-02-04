<?php

class Logger {

	const EOL_SEPARATOR = "\n";
	
	public static function write($sFilename, $aData) {
		// write data to file
		$sData = '';
		foreach ($aData as $lines) {
			$sData .= '"' . implode('" "', $lines) . '"' . self::EOL_SEPARATOR;
		}

		if ($sData) {
			file_put_contents($sFilename, $sData, FILE_APPEND | LOCK_EX);
		}
	}
}