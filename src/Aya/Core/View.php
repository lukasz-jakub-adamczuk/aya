<?php

namespace Aya\Core;

use Aya\Core\Logger;

abstract class View {

    protected $_sViewName;
    
    protected $_sActionName;
    
    protected $_sDaoName;
    
    protected $_sDaoIndex;
    
    protected $_renderer;
    
    
    public function __construct($renderer) {
        $parts = explode('\\', get_class($this));
        $this->_sViewName = str_replace('View', '', array_pop($parts));
        $aExplodedViewName = explode('_', preg_replace('/([a-z])([A-Z0-9])/', "$1_$2", $this->_sViewName));
        $this->_sActionName = array_pop($aExplodedViewName);
        $this->_sDaoName = implode('', $aExplodedViewName);
        $this->_sDaoIndex = strtolower(implode('_', $aExplodedViewName));

        $this->_renderer = $renderer;
    }

    public function run() {
        $this->init();
        
        $this->beforeFill();
        $this->fill();
        $this->afterFill();
    }

    public function init() {}
    
    public function fill() {}

    public function beforeFill() {}

    public function afterFill() {}

    public function redirect($url, $code = 301) {
        Logger::logStandardRequest('redirects');

        header('Location: '.$url, TRUE, $code);
    }

    // copied from Controller
    public function raiseInfo($sMessage) {
        $aMsg = [];
        $aMsg['text'] = $sMessage;
        $aMsg['type'] = 'info';
        $this->_renderer->assign('aMsgs', array($aMsg));
    }

    public function raiseWarning($sMessage) {
        $aMsg = [];
        $aMsg['text'] = $sMessage;
        $aMsg['type'] = 'warning';
        $this->_renderer->assign('aMsgs', array($aMsg));
    }

    public function raiseError($sMessage) {
        $aMsg = [];
        $aMsg['text'] = $sMessage;
        $aMsg['type'] = 'alert';
        $this->_renderer->assign('aMsgs', array($aMsg));
    }
}