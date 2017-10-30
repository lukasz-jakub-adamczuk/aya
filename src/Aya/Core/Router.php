<?php

namespace Aya\Core;

use Aya\Core\Debug;
use Aya\Helper\Text;
use Aya\Helper\ValueMapper;

use Aya\Exception\MissingControllerException;

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

        
        $variablesConf = APP_DIR.'/conf/mvc/variables.conf';
        $conf = parse_ini_file($variablesConf, true);

        // $aUrls = array();
        // foreach ($conf['controllers'] as $key => $value) {
        //     $aUrls[$key] = $value;
        // }

        // $aNames = array();
        // foreach ($conf['names'] as $key => $value) {
        //     $aNames[$key] = $value;
        // }
        // print_r(array_flip($aUrls));
        // print_r($aNames);
        ValueMapper::assignConfig('url', $conf['controllers']);
        ValueMapper::assignConfig('name', $conf['names']);

            // echo $sController;
        if (defined('MVC_MAPPING') && MVC_MAPPING) {
            $sController = ValueMapper::getUrl($sController, true);
            // $sAction = ValueMapper::getUrl($sAction, true);
        }
        // echo $sAction;

        echo $controllerName = Text::toCamelCase($sController).'Controller';

        $controllerFile = CTRL_DIR.'/'.$controllerName.'.php';
        
        if (!file_exists($controllerFile)) {
            // echo 'before';
            // throw new MissingControllerException();
            // echo 'after';
            

            $controllerName = 'FrontController';
            $controllerFile = CTRL_DIR.'/'.$controllerName.'.php';
            require_once $controllerFile;
            $ctrl = APP_NS."\\Controller\\$controllerName";
            $controller = new $ctrl;
            $controller->setTemplateName('404');
        } else {
            require_once $controllerFile;
            $ctrl = APP_NS."\\Controller\\$controllerName";
            $controller = new $ctrl;
        }
        
        
        // $controller = new $ctrl;
        
        $controller->setCtrlName($sController);
        $controller->setActionName($sAction);

        // try {
            // $controller->run();
        // } catch (MissingControllerException $e) {
        //     $this->setTemplateName('404');
        //     Logger::logStandardRequest('404');
        //     // $this->_renderer->assign('content', '404');
        // }

        // if (!file_exists($controllerFile)) {

        // }



        return $controller;
    }
}