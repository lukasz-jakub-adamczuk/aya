<?php

namespace Aya\Debug;

class Panel {

    private static $_vars = [];

    public static function setVar($var, $value) {
        if (!isset(self::$_vars[$var])) {
            self::$_vars[$var] = [];
        }
        self::$_vars[$var][] = $value;
    }

    public static function info() {
        return [
            'vars' => self::$_vars
        ];
    }
}