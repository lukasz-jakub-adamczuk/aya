<?php

namespace Aya\Helper;

use Aya\Core\Dao;

class AvatarManager {

    public static function getAvatar($userSlug) {
        // avatar for editors
        $avatarFile = '/assets/site/redaction/'.$userSlug.'.png';
        if (file_exists(WEB_DIR . $avatarFile)) {
            return $avatarFile;
        }
        // avatar for users
        $avatarFile = '/assets/users/avatars/'.$userSlug.'.png';
        if (file_exists(WEB_DIR . $avatarFile)) {
            return $avatarFile;
        }
        $avatarFile = '/assets/users/no-avatar.jpg';
        if (file_exists(WEB_DIR . $avatarFile)) {
            return $avatarFile;
        }
        return false;
    }
}

