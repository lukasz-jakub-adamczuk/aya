<?php

class Logger {

    const EOL_SEPARATOR = "\n";

    public static $sLogFile;

    private static $_aLogSpaces = array();

    public static function addLogSpace($sKey, $sFile) {
        self::$_aLogSpaces[$sKey] = $sFile;
    }
    
    public static function write($sFilename, $aData) {
        // write data to file
        $sData = '';
        // var_dump($aData);
        foreach ($aData as $lines) {
            $sData = '"' . implode('" "', $lines) . '"' . self::EOL_SEPARATOR;
        }

        if ($sData) {
            $sFileDir = dirname($sFilename);
            if (!file_exists($sFileDir)) {
                mkdir($sFileDir, 0755, true);
            }
            file_put_contents($sFilename, $sData, FILE_APPEND | LOCK_EX);
        }
    }

    public static function logStandardRequest($sPlace) {
        $sHttpReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $sRequestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $sRemoteAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $sHttpUserAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        $aLogs[] = array(date('c'), $sHttpReferer, $sRequestUri, $sRemoteAddr, $sHttpUserAgent);

        if (isset(self::$_aLogSpaces[$sPlace])) {
            self::write(self::$_aLogSpaces[$sPlace], $aLogs);
        } else {
            self::write($sPlace, $aLogs);
        }
    }

}