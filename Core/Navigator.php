<?php
// uniwersalny pojemnik w sesji

class Navigator {

    protected static $_sOwner;
  
	public static function set($sName, $mValue) {
        $_SESSION['_nav_'][$sOwner][$sName] = $mValue;
	}
	
	public static function get($sName) {
	    if (isset($_SESSION['_nav_'][$sOwner][$sName])) {
            return $_SESSION['_nav_'][$sOwner][$sName];
        }
	}
	
	public static function setOwner($sName) {
        self::$_sOwner = $sName;
	}
	
	public static function getOwner() {
        return self::$_sOwner;
	}
	
}

?>
