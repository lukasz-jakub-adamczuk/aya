<?php

abstract class Controller {

    protected $_contentType;

    protected $_params;

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
        // if (file_exists(TPL_DIR.THEME_DIR.DS.$templateName.'.tpl')) {
            $this->_templateName = $templateName;
        // } else {
        //  $this->_templateName = '404';
        // }
    }
    
    public function setView(View $view) {
        $this->_view = $view;
    }

    // getters

    public function getContentType() {
        return $this->_contentType;
    }

    // universal method to get expected name
    public function getName($name, $case = 'caps') {
        // $var = '_s'.ucfirst($name).'Name';
        $var = '_'.$name.'Name';
        $val = $this->$var;
        if ($case == 'caps') {
            $var = '_'.$name.'Name';
            $val = $this->$var;
            return str_replace(' ', '', ucwords(str_replace('-', ' ', $val)));
        } elseif ($case == 'lower') {
            return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $this->$var));
        } else {
            return $this->$val;
        }
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
        // methods executed inside
        // init()
        // _afterInit()
        Debug::show('flow begins...');

        Time::start('controller');

        $this->_contentType = 'html';

        // DB params serialized in constant
        $this->_params = unserialize(DB_SOURCE);

        Time::start('db-init');
        // DB handle
        $this->_db = Db::getInstance($this->_params);
        Time::stop('db-init');

        // session init
        session_start();

        // create container
        User::instance();

        // session_unset($_SESSION['user']);
        
        // template engine
        require_once TPL_ENGINE_DIR.'/libs/Smarty.class.php';
        
        // require_once TPL_ENGINE_DIR.'/libs/Autoloader.php';
        // Smarty_Autoloader::register();

        $this->_renderer = new Smarty;

        // $this->_renderer->caching = 2;
        // $this->_renderer->cache_lifetime = 60;
        // $this->_renderer->compile_check = false;

        Debug::show(TPL_DIR.THEME_DIR, 'template_dir');
        Debug::show(TPL_C_DIR.THEME_DIR, 'template_c_dir');

        $this->_renderer->setTemplateDir(TPL_DIR.THEME_DIR);
        $this->_renderer->setCompileDir(TPL_C_DIR.THEME_DIR);
        $this->_renderer->setConfigDir(APP_DIR.'/configs');
        // $this->_renderer->setCacheDir(APP_DIR.'/cache');
        // $this->setCacheDir(GUESTBOOK_DIR . 'cache');
        Time::stop('smarty-init');

        
        Time::start('ctrl-init');
        $this->init();

        // could be use for authentication
        $this->_afterInit();
        
        Time::stop('ctrl-init');

        // Debug::show($this->_templateName, 'template before auth()');

        Debug::show($this->getCtrlName(), 'ctrl sent to templates in ctrl');
        Debug::show($this->getActionName(), 'ctrl sent to templates in ctrl');
        $this->_renderer->assign('ctrl', $this->getCtrlName());
        $this->_renderer->assign('act', $this->getActionName());

        


        // TODO find better way to transform
        $actionName = ucwords(str_replace('-', ' ', $this->_actionName));

        $aParts = explode(' ', $actionName);
        $aParts[0] = strtolower($aParts[0]);

        $actionName = implode('', $aParts);


        $methodName = $actionName.'Action';

        // echo $methodName;
        $sCacheString = CACHE_DIR . '/html'.str_replace($_SERVER['HTTP_HOST'], '', $_SERVER['REQUEST_URI']).'/index.html';
        // print_r($_SERVER);
        // echo BASE_URL;
        if (CACHE_OUTPUT && file_exists($sCacheString)) {
            $this->_renderer->display($sCacheString);
            // echo file_get_contents($sCacheString);
        } else {
            // controller action
            if (method_exists($this, $methodName)) {
                $this->runBeforeMethod();

                // Debug::show($this->getTemplateName(), 'tpl name in ctrl');
                $this->$methodName();
                // Debug::show($this->getTemplateName(), 'tpl name in ctrl');
                Time::stop('ctrl-method');

                // including view for action
                $viewName = $this->_viewName.'View';
                
                if (file_exists(VIEW_DIR.'/'.$viewName.'.php')) {
                    require_once VIEW_DIR.'/'.$viewName.'.php';
                    
                    $this->_view = new $viewName($this->_renderer);
                    
                    $this->_view->run();
                }
                $this->runAfterMethod();

                // $this->_renderer->assign('ctrl', $this->getCtrlName());
                // $this->_renderer->assign('act', $this->getActionName());
            } else {
                $this->runAfterMethod();

                // 404 // Method not found
                $this->_renderer->assign('content', '404');
            }
            Time::stop('ctrl-action');

            // $this->_renderer->assign('ctrl', $this->_ctrlName);
            // $this->_renderer->assign('act', $this->_actionName);

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

            // print_r(Debug::getLogs());

            if ($this->_contentType != 'html') {
                $output = $this->_renderer->fetch('layout.'.$this->_contentType.'.tpl');
            } else {
                $output = $this->_renderer->fetch('layout.tpl');
            }
            echo $output;

            if (CACHE_OUTPUT) {
                $sCacheDir = dirname($sCacheString);
                if (!file_exists($sCacheDir)) {
                    mkdir($sCacheDir, 0777, true);
                }
                file_put_contents($sCacheString, $output);
            }
        }
    }

    public function init() {
        // $this->setViewName($this->getName('ctrl').$this->getName('action'));
        // echo $this->getCtrlName();
        // echo $this->getActionName();
        $this->setViewName(Text::toPascalCase($this->getCtrlName().' '.$this->getActionName()));

        // echo $this->getViewName();

        Debug::show($this->getViewName(), 'view name in init');

        

        // try '<ctrl_name>-<action_name>.tpl'
        $templateName = $this->getCtrlName('ctrl', 'lower').'-'.$this->getName('action', 'lower');
        Debug::show($templateName, '1. template init');
        if (!file_exists(TPL_DIR.THEME_DIR.DS.$templateName.'.tpl')) {
            // try 'all-<action_name>.tpl'
            $templateName = 'all-'.$this->getActionName();
            Debug::show($templateName, '2. template init');
            if (!file_exists(TPL_DIR.THEME_DIR.DS.$templateName.'.tpl')) {
                // try index.tpl
                $templateName = 'index';
                Debug::show($templateName, '3. template init');
            }
        }
        $this->setTemplateName($templateName);

        
    }

    protected function _afterInit() {
        // Debug::show($this->getTemplateName(), 'tpl name in ctrl');
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
        if (file_exists(CTRL_DIR.DS.$ctrlName.'.php')) {
            require_once CTRL_DIR.DS.$ctrlName.'.php';
            $oCtrl = new $ctrlName;

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
                $oCtrl->runBeforeMethod();

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
                Debug::show($viewName, 'view in actionForward()');
                if (file_exists(VIEW_DIR.DS.$viewName.'.php')) {
                    require_once VIEW_DIR.DS.$viewName.'.php';
                    $oCtrl->_view = new $viewName($this->_renderer);
                    
                    $oCtrl->_view->run();
                }
            
                $oCtrl->runAfterMethod();
            }
        }
        if ($bDieAfterForward) {
            die();
        }
    }
    
    public function runAfterMethod() {
        // defining template name
        Debug::show($this->_templateName, 'runAfterMethod() in ' . $this->getCtrlName() . ' ctrl');
        if ($this->_templateName) {
            $this->_renderer->assign('content', $this->_templateName);
        } else {
            $this->_renderer->assign('content', '404');
        }
    }
}