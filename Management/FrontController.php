<?php
require_once AYA_DIR.'/Core/Controller.php';

class FrontController extends Controller {

    protected function _afterInit() {
        
    }

    public function runBeforeMethod() {
        // Breadcrumbs::add('', 'squarezone.pl', 'icon-home');
        // echo $this->getCtrlName();
        $aItem = array(
            'name' => 'ctrl',
            'url' => ValueMapper::getUrl($this->getCtrlName()),
            'text' => ValueMapper::getName($this->getCtrlName()),
        );
        Breadcrumbs::add($aItem);

        // Breadcrumbs::add($this->_renderer->getgetCtrlName(), $this->getCtrlName());

        // $this->_renderer->assign('ctrl', $this->getCtrlName());
        // $this->_renderer->assign('act', $this->getActionName());

        // echo 'a';

        PostmanNotification::analyzeFeeds();

        $this->_renderer->assign('aCounters', PostmanNotification::getFeedsCounters());
        $this->_renderer->assign('iTotal', PostmanNotification::getFeedsTotal());
    }
    
    // TODO should name init()
    public function runAfterMethod() {
        parent::runAfterMethod();

        // $db = Db::getInstance(unserialize(DB_SOURCE));
        // $db->execute("set names utf8;");
        // echo 'run AfterMEthod';

        if (isset($_SESSION['user'])) {
            $this->_renderer->assign('user', $_SESSION['user']);
        }

        

        $this->_renderer->assign('aBreadcrumbs', Breadcrumbs::get());
        
        // vars in templates
        $this->_renderer->assign('base', BASE_URL);
        if (defined('SITE_URL')) {
            $this->_renderer->assign('site', SITE_URL);
        }

        // Debug::show($this->getCtrlName(), 'ctrl sent to tempplates');
        // Debug::show($this->getActionName(), 'ctrl sent to tempplates');
        // $this->_renderer->assign('ctrl', $this->getCtrlName());
        // $this->_renderer->assign('act', $this->getActionName());

        // $this->_renderer->assign('ctrl', $_GET['ctrl']);
        // $this->_renderer->assign('act', $_GET['act']);

        
    }
    
    public function indexAction() {}

    public function infoAction() {}

    // action to set special params in session
    public function setAction() {
        if (DEBUG_MODE) {
            if (isset($_GET['param']) && isset($_GET['value'])) {
                $_SESSION['_params_'][strip_tags($_GET['param'])] = strip_tags($_GET['value']);
            }
        }
    }

    // action to reset/remove special params in session
    public function resetAction() {
        if (DEBUG_MODE) {
            if (isset($_SESSION['_params_'][strip_tags($_GET['param'])])) {
                unset($_SESSION['_params_'][strip_tags($_GET['param'])]);
            }
        }
    }
}