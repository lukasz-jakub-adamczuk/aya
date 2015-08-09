<?php

class HtmlForm {

	private $_sId;

	private $_sAction;
	
	private $_sMethod = 'post';
	
	private $_sEnctype;
	
	private $_sLang = 'pl';

	private $_sCacheDir = null;

	public function __construct($sId, $aParams = null) {
		$this->_sId = $sId;

		print_r($aParams);
	}

	public function configure() {
		
	}

	protected function _init() {
		
	}

	public function setCacheDir($sCacheDir) {
		$this->_sCacheDir = $sCacheDir;
	}

	

	public function render() {
		return $this->_renderForm();
	}

	private function _renderForm() {
		$s = '';
		$s .= '<form id="'.$this->_sId.'">';
		$s .= '<input id="" name="" type="text" value="aaa" />';
		$s .= '</form>';
		return $s;
	}
}
