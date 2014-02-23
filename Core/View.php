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

	public function init() {
		$this->beforeFill();
		Time::stop('view-before-fill');
		$this->fill();
		Time::stop('view-fill');
		$this->afterFill();
		Time::stop('view-after-fill');
	}
	
	public function fill() {}

	public function beforeFill() {}

	public function afterFill() {}
}