<?php

namespace Aya\Helper;

class Text {

    public static function toPascalCase($text) {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $text)));
    }

    public static function toCamelCase($text) {
        //return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $text));
        return str_replace('-', '', ucwords($text, '-'));
    }

    public static function toLowerCase($text) {
        return strtolower($text);
    }

    public static function slugify($text) { 
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        
        // trim
        $text = trim($text, '-');
        
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        
        // lowercase
        $text = strtolower($text);
        
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        
        if (empty($text)) {
            return 'n-a';
        }
        
        return $text;
    }
}