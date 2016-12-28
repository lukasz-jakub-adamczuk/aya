<?php

// common container for User info
class User {

    protected static $_instance;
    
    public static $_aPermissions;

    public static $_aUser;

    private function __construct($aUser) {
        self::$_aUser = $aUser;
    }

    public static function instance($aUser = null) {
        if (is_null(self::$_instance)) {
            self::$_instance = new User($aUser);
        }
        return self::$_instance;
    }

    // getters
    public static function name() {
        return self::$_aUser['name'];
    }

    public static function checkAccess() {

    }
}