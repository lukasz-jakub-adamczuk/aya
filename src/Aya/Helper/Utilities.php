<?php

namespace Aya\Helpers;

class Utilities {

    public static function slugify($sText) { 
        // replace non letter or digits by -
        $sText = preg_replace('~[^\\pL\d]+~u', '-', $sText);
        
        // trim
        $sText = trim($sText, '-');
        
        // transliterate
        $sText = iconv('utf-8', 'us-ascii//TRANSLIT', $sText);
        
        // lowercase
        $sText = strtolower($sText);
        
        // remove unwanted characters
        $sText = preg_replace('~[^-\w]+~', '', $sText);
        
        if (empty($sText)) {
            return 'n-a';
        }
        
        return $sText;
    }
}