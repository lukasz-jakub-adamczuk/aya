<?php

// common container for User info
class User {

    protected static $_instance;
    
    public static $_aPermissions;

    public static $_aUser = null;

    private function __construct() {
        if (isset($_SESSION['user'])) {
            self::$_aUser = $_SESSION['user'];
        }
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new User();
        }
        return self::$_instance;
    }

    public static function set($aUser = null) {
        // set value when is null (log in) or check is not null (exists)
        if (is_null(self::$_aUser)) {
            self::$_aUser = $aUser;
        } else {
            return true;
        }
    }

    public static function get() {
        return self::$_aUser;
    }

    public static function reset() {
        self::$_aUser = null;
    }

    // getters
    public static function id() {
        return self::$_aUser['id'];
    }

    public static function name() {
        return self::$_aUser['name'];
    }

    public static function group() {
        return self::$_aUser['group'];
    }

    // checkers

    public static function checkAccess() {

    }

    public static function grantAccess($sGroup) {
        if (self::$_aUser) {

        }
    }

    public static function atLeast($sGroup) {
        switch ($sGroup) {
            case 'admin':
                return self::group() === 'admin';
                // break;
            case 'moderator':
                return in_array(self::group(), array('moderator', 'admin'));

            case 'editor':
                return in_array(self::group(), array('editor', 'moderator', 'admin'));

            case 'member':
                return in_array(self::group(), array('member', 'editor', 'moderator', 'admin'));

            case 'user':
                return in_array(self::group(), array('user', 'member', 'editor', 'moderator', 'admin'));

            default:
                return false;
        }
    }
}