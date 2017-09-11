<?php

namespace Aya\Core;

use Aya\Core\Debug;
use Aya\Helper\ValueMapper;

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

        
        $sVariablesConf = APP_DIR.'/conf/mvc/variables.conf';
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
        if (defined('MVC_MAPPING') && MVC_MAPPING) {
            $sController = ValueMapper::getUrl($sController, true);
            // $sAction = ValueMapper::getUrl($sAction, true);
        }
        // echo $sAction;

        $sControllerName = str_replace(' ', '', ucwords(str_replace('-', ' ', $sController))).'Controller';

        $controllerFile = CTRL_DIR.'/'.$sControllerName.'.php';
        
        // try {
            require_once $controllerFile;
            $ctrl = APP_NS."\\Controller\\$sControllerName";
            $oController = new $ctrl;
            // $oController = new Renaissance\Controller\{$sControllerName};
            
            $oController->setCtrlName($sController);
            $oController->setActionName($sAction);
        // } catch (MissingControllerException $e) {
            // what else
        // }

        $oController->run();

        return $oController;
    }
}