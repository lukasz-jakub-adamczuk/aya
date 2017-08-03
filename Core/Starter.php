<?php

class Starter {

    public function __autoload($slassName) {
        // require_once('./' . $slassName . '.php');
        require_once AYA_DIR.'/Core/'.$slassName.'.php';
    }

    public static function init() {
        require_once AYA_DIR.'/Core/Time.php';
        require_once AYA_DIR.'/Core/Logger.php';
        require_once AYA_DIR.'/Core/Debug.php';

        require_once AYA_DIR.'/Core/Text.php';

        require_once AYA_DIR.'/Core/Db.php';
        require_once AYA_DIR.'/Core/Dao.php';
        require_once AYA_DIR.'/Core/User.php';
        require_once AYA_DIR.'/Core/Navigator.php';
        require_once AYA_DIR.'/Core/MessageList.php';
        require_once AYA_DIR.'/Core/Router.php';
    }
}