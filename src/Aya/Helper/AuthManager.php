<?php

namespace Aya\Helper;

use Aya\Core\Dao;
use Aya\Core\User;
use Aya\Helper\AvatarManager;

class AuthManager {

    public static function login() {
        if (isset($_POST['auth'])) {
            $username = isset($_POST['auth']['user']) ? $_POST['auth']['user'] : '';
            $password = isset($_POST['auth']['pass']) ? $_POST['auth']['pass'] : '';
            if (!empty($username) && !empty($password)) {
                $userEntity = Dao::entity('user');
                
                $user = $userEntity->authenticateUser($username, $password);

                if ($user) {
                    return AuthManager::setUser($user);
                }
            }
        }
        return false;
    }

    public static function logout() {
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
            User::reset();
            return true;
        }
        return false;
    }

    public static function setUser($user) {
        $_SESSION['user']['id'] = $user['id_user'];
        $_SESSION['user']['name'] = $user['name'];
        $_SESSION['user']['slug'] = $user['slug'];
        $_SESSION['user']['active'] = $user['active'];
        $_SESSION['user']['group'] = isset($user['group_slug']) ? $user['group_slug'] : '';
        $_SESSION['user']['perm'] = isset($user['sz_perm']) ? $user['sz_perm'] : '';
        $_SESSION['user']['avatar'] = $sAvatarFile = AvatarManager::getAvatar($user['slug']);
        
        // set user container after login
        User::set($_SESSION['user']);
        return true;
    }
}