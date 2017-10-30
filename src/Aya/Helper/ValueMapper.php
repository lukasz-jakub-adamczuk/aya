<?php

namespace Aya\Helper;

class ValueMapper {

    private static $_values;

    static public function assignConfig($name, $values) {
        self::$_values[$name] = $values;
        self::$_values[$name.'-inverted'] = array_flip($values);
    }

    static public function getName($key, $bInverted = false) {
        return self::_get($key, 'name', $bInverted);
    }

    static public function getUrl($key, $bInverted = false) {
        return self::_get($key, 'url', $bInverted);
    }

    static public function hasValue($key) {
        // if ()
        return self::_get($key, 'url', $bInverted);
    }

    static public function allValues() {
        return self::$_values;
    }

    static private function _get($key, $name, $bInverted = false) {
        if ($bInverted) {
            if (isset(self::$_values[$name.'-inverted'][$key])) {
                return self::$_values[$name.'-inverted'][$key];
            } else {
                return $key;
            }
        } else {
            if (isset(self::$_values[$name][$key])) {
                return self::$_values[$name][$key];
            } else {
                return $key;
            }
        }
    }
}