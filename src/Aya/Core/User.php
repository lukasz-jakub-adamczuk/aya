<?php

namespace Aya\Core;

class User {

    protected static $_instance;
    
    public static $_aPermissions;

    public static $_user = null;

    private function __construct() {
        if (isset($_SESSION['user'])) {
            self::$_user = $_SESSION['user'];
        }
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new User();
        }
        return self::$_instance;
    }
    
    public static function get($key = null) {
        if ($key) {
            return isset(self::$_user[$key]) ? self::$_user[$key] : null;
        }
        return self::$_user;
    }

    public static function set($user = null) {
        // set value when is null (before log in) or check is not null (after log in)
        if (is_null(self::$_user)) {
            self::$_user = $user;
            return false;
        } else {
            return true;
        }
        // return false;
    }

    // public

    public static function reset() {
        self::$_user = null;
    }

    // getters
    public static function id() {
        return self::$_user['id'];
    }

    public static function name() {
        return self::$_user['name'];
    }

    public static function group() {
        return self::$_user['perm'] || null;
    }


    public static function getId() {
        return self::$_user['id'];
    }

    public static function getName() {
        return self::$_user['name'];
    }

    public static function getSlug() {
        return self::$_user['slug'];
    }
    // checkers

    // public static 

    public static function checkAccess() {

    }

    public static function grantAccess($sGroup) {
        if (self::$_user) {

        }
    }

    public static function atLeast($sGroup) {
        switch ($sGroup) {
            case 'admin':
                return self::group() == 'admin';
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