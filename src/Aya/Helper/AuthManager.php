<?php

namespace Aya\Helper;

use Aya\Core\Dao;
use Aya\Core\User;
use Aya\Helper\AvatarManager;
use Aya\Helper\MessageList;

use Aya\Exception\MissingEntityException;

class AuthManager {

    public static function login() {
        if (isset($_POST['auth'])) {
            $username = isset($_POST['auth']['user']) ? $_POST['auth']['user'] : '';
            $password = isset($_POST['auth']['pass']) ? $_POST['auth']['pass'] : '';
            if (!empty($username) && !empty($password)) {
                $userEntity = Dao::entity('user');
                
                try {
                    $user = $userEntity->authenticateUser($username, $password);
                    // echo 'entity:'; print_r($userEntity);
                    // echo 'USER'; print_r($user);
                    if ($user) {
                        return AuthManager::setUser($user);
                    }
                } catch (MissingEntityException $e) {
                    // not found user
                    MessageList::raiseError('Nieprawidłowa nazwa użytkownika lub hasło.');
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
        $_SESSION['user']['avatar'] = AvatarManager::getAvatar($user['slug']);
        
        // set user container after login
        User::set($_SESSION['user']);
        return true;
    }
}