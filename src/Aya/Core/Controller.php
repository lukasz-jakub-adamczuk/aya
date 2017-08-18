<?php

namespace Aya\Core;

use Aya\Core\Db;
use Aya\Core\Logger;
use Aya\Core\User;
use Aya\Debug\Panel;
use Aya\Exception\MissingEntityException;
use Aya\Helper\MessageList;
use Aya\Helper\ValueMapper;
use Aya\Helper\Text;
use Aya\Helper\Time;

use \Smarty;

abstract class Controller {

    protected $_contentType;

    protected $_ctrlName;
    
    protected $_actionName;
    
    protected $_viewName;
    
    protected $_templateName;
    
    protected $_db;

    protected $_view;
    
    protected $_renderer;

    public function __construct() {}

    // setters

    public function setContentType($contentType) {
        $this->_contentType = $contentType;
    }
    
    public function setCtrlName($ctrlName) {
        $this->_ctrlName = $ctrlName;
    }

    public function setActionName($actionName) {
        $this->_actionName = $actionName;
    }
    
    public function setViewName($viewName) {
        $this->_viewName = $viewName;
    }
    
    public function setTemplateName($templateName) {
        $this->_templateName = $templateName;
    }
    
    public function setView(View $view) {
        $this->_view = $view;
    }

    // getters
    public function getContentType() {
        return $this->_contentType;
    }
    
    public function getCtrlName() {
        return $this->_ctrlName;
    }

    public function getActionName() {
        return $this->_actionName;
    }
    
    public function getViewName() {
        return $this->_viewName;
    }

    public function getTemplateName() {
        return $this->_templateName;
    }

    public function run() {
        Debug::show('flow begins...');

        Time::start('controller');

        $this->_contentType = 'html';

        Time::start('db-init');
        // DB handle
        $this->_db = Db::getInstance(unserialize(DB_SOURCE));
        Time::stop('db-init');

        // session init
        session_start();

        // create container
        User::instance();

        // template engine
        require_once TPL_ENGINE_DIR.'/libs/Smarty.class.php';

        $this->_renderer = new Smarty;

        Debug::show(TPL_DIR.THEME_DIR, 'template_dir');
        Debug::show(TPL_C_DIR.THEME_DIR, 'template_c_dir');

        $this->_renderer->setTemplateDir(TPL_DIR.THEME_DIR);
        $this->_renderer->setCompileDir(TPL_C_DIR.THEME_DIR);
        $this->_renderer->setConfigDir(APP_DIR.'/configs');
        // $this->_renderer->setCacheDir(APP_DIR.'/cache');
        Time::stop('smarty-init');
        
        Time::start('ctrl-init');
        $this->init();

        // could be use for authentication
        $this->_afterInit();
        
        Time::stop('ctrl-init');

        $this->_renderer->assign('ctrl', $this->getCtrlName());
        $this->_renderer->assign('act', $this->getActionName());
        
        Panel::setVar('ctrl', $this->getCtrlName());
        Panel::setVar('act', $this->getActionName());
        Debug::show($this->getCtrlName(), 'ctrl name sent to templates in ctrl');
        Debug::show($this->getActionName(), 'act name sent to templates in ctrl');
        
        $sCacheString = CACHE_DIR . '/html'.str_replace($_SERVER['HTTP_HOST'], '', $_SERVER['REQUEST_URI']).'/index.html';
        if (CACHE_OUTPUT && file_exists($sCacheString)) {
            $this->_renderer->display($sCacheString);
        } else {
            $this->beforeAction();

            $action = Text::toPascalCase($this->_actionName).'Action';

            // controller action
            if (method_exists($this, $action)) {
                

                $this->$action();
                Time::stop('ctrl-action');

                Panel::setVar('view', $this->getViewName());

                // including view for action
                $viewName = $this->_viewName.'View';
                $viewFile = VIEW_DIR.'/'.$viewName.'.php';
                
                if (file_exists($viewFile)) {
                    require_once $viewFile;
                    $view = APP_NS."\\View\\$viewName";

                    $this->_view = new $view($this->_renderer);
                    
                    try {
                        $this->_view->run();
                    } catch (MissingEntityException $e) {
                        $this->setTemplateName('404');
                        Logger::logStandardRequest('404');
                        // $this->_renderer->assign('content', '404');
                    }
                }
            }

            $this->afterAction();

            Time::stop('ctrl-action');

            Debug::show('flow ends...');

            Time::stop('controller');

            Time::total(true);

            Debug::show(Time::stats(), 'Time stats');

            Time::stop();
            $this->_renderer->assign('sServerTime', Time::get());
            
            // assign debug info
            $this->_renderer->assign('aLogs', Debug::getLogs());

            // assign messages
            $this->_renderer->assign('aMsgs', MessageList::get());

            $this->_renderer->assign('debugPanel', Panel::info());

            if ($this->_contentType != 'html') {
                $output = $this->_renderer->fetch('layout.'.$this->_contentType.'.tpl');
            } else {
                $output = $this->_renderer->fetch('layout.tpl');
            }
            
            if (CACHE_OUTPUT) {
                $sCacheDir = dirname($sCacheString);
                if (!file_exists($sCacheDir)) {
                    mkdir($sCacheDir, 0777, true);
                }
                file_put_contents($sCacheString, $output);
            }
            
            echo $output;
        }
    }

    public function init() {
        $this->setViewName(Text::toPascalCase($this->getCtrlName().' '.$this->getActionName()));

        Debug::show($this->getViewName(), 'view name in init');

        // try '<ctrl_name>-<action_name>.tpl'
        $templateName = $this->getCtrlName().'-'.Text::toLowerCase($this->getActionName());
        Debug::show($templateName, '1. template init');
        if (!file_exists(TPL_DIR.THEME_DIR.DS.$templateName.'.tpl')) {
            // try 'all-<action_name>.tpl'
            $templateName = 'all-'.Text::toLowerCase($this->getActionName());
            Debug::show($templateName, '2. template init');
            if (!file_exists(TPL_DIR.THEME_DIR.DS.$templateName.'.tpl')) {
                // try index.tpl
                $templateName = 'index';
                Debug::show($templateName, '3. template init');
            }
        }
        $this->setTemplateName($templateName);

        Panel::setVar('tpl', $this->getTemplateName());
    }

    protected function _afterInit() {
        // auth or not ?
		if (AUTH_MODE) {
			// if (isset($_SESSION['user'])) {
			if (User::set()) {
				$this->actionForward('index', 'auth');
			} else {
				$this->actionForward('index', 'auth', true);
			}
		}
    }

    // TODO clean... maybe refactor
    public function actionForward($sAction, $sCtrl = null, $bOverrideTemplate = false, $params = null, $bDieAfterForward = false) {
        Debug::show($this->_templateName, 'template before actionForward()');
        $sCtrl = is_null($sCtrl) ? $_GET['ctrl'] : $sCtrl;

        // set additional params
        if ($params) {
            foreach ($params as $pk => $param) {
                $aParts = explode(':', $pk);
                if ($aParts[0] == 'get') {
                    $_GET[$aParts[1]] = $param;
                }
            }
        }
        // print_r($params);
        Debug::show($params, 'params from redirect action');
        Debug::show($_GET, 'params assigned to $_GET');

        $ctrlName = str_replace(' ', '', ucwords(str_replace('-', ' ', $sCtrl))).'Controller';
        Debug::show($ctrlName, 'ctrl in actionForward()');

        $ctrlFile = CTRL_DIR.'/'.$ctrlName.'.php';

        if (file_exists($ctrlFile)) {
            require_once $ctrlFile;
            $ctrl = APP_NS."\\Controller\\$ctrlName";
            $oCtrl = new $ctrl;

            $oCtrl->setCtrlName($sCtrl);
            $oCtrl->setActionName($sAction);

            $oCtrl->init();
            
            $oCtrl->_renderer = $this->_renderer;

        
            $methodName = $sAction.'Action';
            Debug::show($methodName, 'method in actionForward()');

            Debug::show($this->getTemplateName(), 'template in actionForward() $this ctrl');
            Debug::show($oCtrl->getTemplateName(), 'template in actionForward() $oCtrl ctrl');
            Debug::show(array('ctrl' => $ctrlName, 'method' => $methodName), 'ctrl and method in actionForward()');
            if (method_exists($oCtrl, $methodName)) {
                $oCtrl->beforeAction();

                // action method in ctrl
                $oCtrl->$methodName();

                if ($bOverrideTemplate) {
                    // set template name to parent initial ctrl
                    $this->setTemplateName($oCtrl->getTemplateName());

                    Debug::show($oCtrl->getCtrlName(), 'ctrl sent to tempplates in actionForward');
                    Debug::show($oCtrl->getActionName(), 'ctrl sent to tempplates in actionForward');
                    $this->_renderer->assign('ctrl', $oCtrl->getCtrlName());
                    $this->_renderer->assign('act', $oCtrl->getActionName());
                }
                
                // view including
                $ctrlName = str_replace(' ', '', ucwords(str_replace('-', ' ', $sCtrl)));
                $actionName = str_replace(' ', '', ucwords(str_replace('-', ' ', $sAction)));
                $viewName = $ctrlName.$actionName.'View';
                $viewFile = VIEW_DIR.'/'.$viewName.'.php';
                Debug::show($viewName, 'view in actionForward()');
                if (file_exists($viewFile)) {
                    require_once $viewFile;
                    $view = APP_NS."\\View\\$viewName";
                    $oCtrl->_view = new $view($this->_renderer);
                    
                    try {
                        $oCtrl->_view->run();
                    } catch (MissingEntityException $e) {
                        $oCtrl->setTemplateName('404');
                        Logger::logStandardRequest('404');
                        // $this->_renderer->assign('content', '404');
                    }
                }
            
                $oCtrl->afterAction();
            }
        }
        if ($bDieAfterForward) {
            die();
        }
    }

    public function beforeAction() {}
    
    public function afterAction() {
        // defining template name
        Debug::show($this->_templateName, 'afterAction() in ' . $this->getCtrlName() . ' ctrl');
        if ($this->_templateName) {
            $this->_renderer->assign('content', $this->_templateName);
        } else {
            $this->_renderer->assign('content', '404');
        }
    }
}