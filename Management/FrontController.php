<?php
require_once AYA_DIR.'/Core/Controller.php';

class FrontController extends Controller {
	
	// TODO should name init()
	public function runAfterMethod() {
		parent::runAfterMethod();

		// $db = Db::getInstance(unserialize(DB_SOURCE));
		// $db->execute("set names utf8;");

		if (isset($_SESSION['user'])) {
			$this->_oRenderer->assign('user', $_SESSION['user']);
		}
		
		// vars in templates
		$this->_oRenderer->assign('base', BASE_URL);
		$this->_oRenderer->assign('ctrl', $this->getCtrlName());
		$this->_oRenderer->assign('act', $this->getActionName());
	}
	
	public function indexAction() {
		
	}

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