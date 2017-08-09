<?php

namespace Aya\Helper;

class Breadcrumbs {

    static private $_aValues = [];

    static public function add($aItem) {
        if (isset($aItem['name'])) {
            self::$_aValues[$aItem['name']] = $aItem;
        } else {
            self::$_aValues[] = $aItem;
        }
    }

    static public function get() {
        return self::$_aValues;
    }
}