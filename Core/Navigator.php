<?php
// uniwersalny pojemnik w sesji

class Navigator {

    protected static $_sOwner;
    
    public static function init() {
        // retrive data from get
        if (isset($_GET['nav'])) {
            foreach ($_GET['nav'] as $key => $val) {
                $_SESSION['_nav_'][self::$_sOwner][$key] = $val !== 'null' ? $val : 'null';
            }
        }
        // retrive data from post
        if (isset($_POST['nav'])) {
            foreach ($_POST['nav'] as $key => $val) {
                $_SESSION['_nav_'][self::$_sOwner][$key] = $val !== 'null' ? $val : 'null';
            }
        }
    }
    
    public static function load() {
        if (isset($_SESSION['_nav_'][self::$_sOwner])) {
            return $_SESSION['_nav_'][self::$_sOwner];
        }
        return array();
    }
	
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

