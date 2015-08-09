<?php

abstract class View {

	protected $_sViewName;
	
	protected $_sActionName;
	
	protected $_sDaoName;
	
	protected $_sDaoIndex;
	
	protected $_oRenderer;
	
	
	public function __construct($oRenderer) {
		$this->_sViewName = str_replace('View', '', get_class($this));
		$aExplodedViewName = explode('_', preg_replace('/([a-z])([A-Z0-9])/', "$1_$2", $this->_sViewName));
		$this->_sActionName = array_pop($aExplodedViewName);
		$this->_sDaoName = implode('', $aExplodedViewName);
		$this->_sDaoIndex = strtolower(implode('_', $aExplodedViewName));

		$this->_oRenderer = $oRenderer;
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

	// copied from Controller
	public function raiseInfo($sMessage) {
		$aMsg = array();
		$aMsg['text'] = $sMessage;
		$aMsg['type'] = 'info';
		$this->_oRenderer->assign('aMsgs', array($aMsg));
	}

	public function raiseWarning($sMessage) {
		$aMsg = array();
		$aMsg['text'] = $sMessage;
		$aMsg['type'] = 'warning';
		$this->_oRenderer->assign('aMsgs', array($aMsg));
	}

	public function raiseError($sMessage) {
		$aMsg = array();
		$aMsg['text'] = $sMessage;
		$aMsg['type'] = 'alert';
		$this->_oRenderer->assign('aMsgs', array($aMsg));
	}
}