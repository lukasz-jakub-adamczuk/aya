<?php
// uniwersalny pojemnik w sesji

class Navigator {

    protected static $_sOwner;
	
	public static function set($sName, $mValue) {
        $_SESSION['_nav_'][self::$_sOwner][$sName] = $mValue;
	}
	
	public static function get($sName) {
	    if (self::is($sName)) {
            return $_SESSION['_nav_'][self::$_sOwner][$sName];
        } else {
            return false;
        }
	}
	
	public static function is($sName) {
	    if (isset($_SESSION['_nav_'][self::$_sOwner][$sName])) {
            return true;
        } else {
            return false;
        }
	}
	
	public static function setOwner($sName) {
        self::$_sOwner = $sName;
	}
	
	public static function getOwner() {
        return self::$_sOwner;
	}
	
}

