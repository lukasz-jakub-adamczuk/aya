<?php

namespace Aya\Core;

use Aya\Core\Debug;
use Aya\Helper\ValueMapper;
// use Renaissance\Controller as Controller;

class Router {

    public static function init() {
        // sprawdzenie urla
        if (isset($_GET['ctrl'])) {
            $sController = strip_tags($_GET['ctrl']);
        } else {
            $sController = DEFAULT_CONTROLLER;
        }
        if (isset($_GET['act'])) {
            $sAction = strip_tags($_GET['act']);
        } else {
            $sAction = DEFAULT_ACTION;
        }

        Debug::show($sController, '$_GET[ctrl] in Router');
        Debug::show($sAction, '$_GET[act] in Router');

        if (defined('MVC_MAPPING') && MVC_MAPPING) {
            $sVariablesConf = APP_DIR.'/configs/variables.conf';
            $aConf = parse_ini_file($sVariablesConf, true);

            $aUrls = array();
            foreach ($aConf['controllers'] as $key => $value) {
                $aUrls[$key] = $value;
            }

            $aNames = array();
            foreach ($aConf['names'] as $key => $value) {
                $aNames[$key] = $value;
            }
            // print_r(array_flip($aUrls));
            // print_r($aNames);
            ValueMapper::assignConfig('url', $aUrls);
            ValueMapper::assignConfig('name', $aNames);

            // echo $sController;

            $sController = ValueMapper::getUrl($sController, true);
            // $sAction = ValueMapper::getUrl($sAction, true);
        }
        // echo $sAction;

        $sControllerName = str_replace(' ', '', ucwords(str_replace('-', ' ', $sController))).'Controller';

        $controllerFile = CTRL_DIR.'/'.$sControllerName.'.php';
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $ctrl = "Renaissance\\Controller\\$sControllerName";
            $oController = new $ctrl;
            // $oController = new Renaissance\Controller\{$sControllerName};
            
            $oController->setCtrlName($sController);
            $oController->setActionName($sAction);
        } else {
            if (file_exists(CTRL_DIR.'/ErrorController.php')) {
                require_once CTRL_DIR.'/ErrorController.php';
            } else {
                // require_once AYA_DIR.'/Core/ErrorController.php';
                require_once AYA_DIR.'/src/Aya/Core/ErrorController.php';
            }
            $oController = new ErrorController;
        }

        $oController->run();

        return $oController;
    }
}