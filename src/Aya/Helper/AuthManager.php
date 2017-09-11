<?php

namespace Aya\Helper;

use Aya\Core\Dao;
use Aya\Core\User;
use Aya\Helper\AvatarManager;

class AuthManager {

    public static function login() {
        if (isset($_POST['auth'])) {
            $sUser = isset($_POST['auth']['user']) ? $_POST['auth']['user'] : '';
            $sPass = isset($_POST['auth']['pass']) ? $_POST['auth']['pass'] : '';
            if (!empty($sUser) && !empty($sPass)) {
                $sql = 'SELECT u.*, ug.slug group_slug, ug.name group_name, up.*
                        FROM user u
                        LEFT JOIN user_group ug ON(ug.id_user_group=u.id_user_group)
                        LEFT JOIN user_permission up ON(up.id_user=u.id_user)
                        WHERE u.name="'.addslashes($sUser).'" AND u.hash="'.sha1(addslashes(strtolower($sUser)).addslashes($sPass)).'"';
                $oEntity = Dao::entity('user');
                $oEntity->query($sql);

                $oEntity->load();

                $aUser = $oEntity->getFields();

                if ($aUser) {
                    $_SESSION['user']['id'] = $aUser['id_user'];
                    $_SESSION['user']['name'] = $aUser['name'];
                    $_SESSION['user']['slug'] = $aUser['slug'];
                    $_SESSION['user']['active'] = $aUser['active'];
                    $_SESSION['user']['group'] = isset($aUser['group_slug']) ? $aUser['group_slug'] : '';
                    $_SESSION['user']['perm'] = isset($aUser['sz_perm']) ? $aUser['sz_perm'] : '';
                    $_SESSION['user']['avatar'] = $sAvatarFile = AvatarManager::getAvatar($aUser['slug']);
                    
                    // set user container after login
                    User::set($_SESSION['user']);
                    return true;
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
}