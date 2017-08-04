<?php

namespace Aya\Helper;

class Text {

    public static function toPascalCase($text) {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $text)));
    }

    public static function toCamelCase($text) {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $text));
    }
}