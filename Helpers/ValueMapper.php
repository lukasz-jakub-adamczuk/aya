<?php

class ValueMapper {

    private static $_aValues;

    static public function assignConfig($sName, $aValues) {
        self::$_aValues[$sName] = $aValues;
        self::$_aValues[$sName.'-inverted'] = array_flip($aValues);
    }

    static public function getName($sKey, $bInverted = false) {
        return self::_get($sKey, 'name', $bInverted);
    }

    static public function getUrl($sKey, $bInverted = false) {
        return self::_get($sKey, 'url', $bInverted);
    }

    static private function _get($sKey, $sName, $bInverted = false) {
        if ($bInverted) {
            if (isset(self::$_aValues[$sName.'-inverted'][$sKey])) {
                return self::$_aValues[$sName.'-inverted'][$sKey];
            } else {
                return $sKey;
            }
        } else {
            if (isset(self::$_aValues[$sName][$sKey])) {
                return self::$_aValues[$sName][$sKey];
            } else {
                return $sKey;
            }
        }
    }
}