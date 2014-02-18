<?php
require_once SMARTY_DIR.'/Smarty.class.php';

abstract class Controller {

	protected $_aParams;

	protected $_sCtrlName;
	
	protected $_sActionName;
	
	protected $_sViewName;
	
	protected $_sTemplateName;
	
	protected $_db;

	protected $_oView;
	
	protected $_oRenderer;

	public function __construct() {}

	// setters
	
	public function setCtrlName($sCtrlName) {
		$this->_sCtrlName = $sCtrlName;
	}

	public function setActionName($sActionName) {
		$this->_sActionName = $sActionName;
	}
	
	public function setViewName($sViewName) {
		$this->_sViewName = $sViewName;
	}
	
	public function setTemplateName($sTemplateName) {
		$this->_sTemplateName = $sTemplateName;
	}
	
	public function setView(View $oView) {
		$this->_oView = $oView;
	}

	// getters

	// universal method to get expected name
	public function getName($sName, $sCase = 'caps') {
		$sVar = '_s'.ucfirst($sName).'Name';
		$sVal = $this->$sVar;
		if ($sCase == 'caps') {
			return str_replace(' ', '', ucwords(str_replace('-', ' ', $this->$sVar)));
		} elseif ($sCase == 'lower') {
			return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $this->$sVar));
		} else {
			return $this->$sVar;
		}
	}
	
	public function getCtrlName() {
		return $this->_sCtrlName;
	}

	public function getActionName() {
		return $this->_sActionName;
	}
	
	public function getViewName() {
		return $this->_sViewName;
	}

	public function init() {
		$this->setViewName($this->getName('ctrl').$this->getName('action'));

		if (file_exists(TPL_DIR.THEME_DIR.DS.$this->getCtrlName().'-'.$this->getActionName().'.tpl')) {
			$this->setTemplateName($this->getCtrlName().'-'.$this->getActionName());
		} else {
			if (file_exists(TPL_DIR.THEME_DIR.'/all-'.$this->getActionName().'.tpl')) {
				$this->setTemplateName('all-'.$this->getActionName());
			} else {
				$this->setTemplateName('index');
			}
		}
	}

	public function run() {
		// methods executed inside
		// init()
		// _afterInit()

		// DB params serialized in constant
		$this->_aParams = unserialize(DB_SOURCE);

		// DB handle
		$this->_db = Db::getInstance($this->_aParams);

		// session init
		session_start();

		// template engine
		$this->_oRenderer = new Smarty;

		Debug::show(TPL_DIR.THEME_DIR);
		Debug::show(TPL_C_DIR.THEME_DIR);

		$this->_oRenderer->setTemplateDir(TPL_DIR.THEME_DIR);
		$this->_oRenderer->setCompileDir(TPL_C_DIR.THEME_DIR);
		// $this->setConfigDir(GUESTBOOK_DIR . 'configs');
		// $this->setCacheDir(GUESTBOOK_DIR . 'cache');
  
		// auth or not ?
		if (!isset($_SESSION['user'])) {
			echo 'unauthorized';
			// $this->_a
			// $this->_oRenderer->display('auth.tpl');
			// $this->actionForward('login', 'auth');
			

			// $this->auth();

			// die();	
			// echo $_SESSION['auth'];
		} else {
			echo 'authorized';
		}

		$this->init();

		$this->_afterInit();

		// TODO find better way to transform
		$sActionName = ucwords(str_replace('-', ' ', $this->_sActionName));

		$aParts = explode(' ', $sActionName);
		$aParts[0] = strtolower($aParts[0]);

		$sActionName = implode('', $aParts);


		$sMethodName = $sActionName.'Action';
		
		// controller action
		if (method_exists($this, $sMethodName)) {
			// insert, update, etc.
			// if (isset($_POST['act'])) {
			// 	$sAction = current(array_keys($_POST['act']));
			// 	if (isset($_GET['action']) && $_GET['action'] !== 'index') {
			// 		$sMethodName = $sAction.ucfirst($_GET['action']).'Action';
			// 		$sTplName = $this->getControllerName('lower').'-'.$_GET['action'].'-'.$sAction;
			// 	} else {
			// 		$sActionName = $sAction.'Action';
			// 		$sTplName = $this->getControllerName('lower').'-'.$sAction;
			// 	}
	
			// 	if (method_exists($this, $sActionName)) {
			// 		$this->setTemplateName($sTplName);
			// 		$this->$sActionName();
			// 	}
			// } else {
				
			// 	$this->$sMethodName();
			// }
			$this->$sMethodName();

			// including view for action
			$sViewName = $this->_sViewName.'View';
			if (file_exists(VIEW_DIR.'/'.$sViewName.'.php')) {
				require_once VIEW_DIR.'/'.$sViewName.'.php';
				$this->_oView = new $sViewName($this->_oRenderer);
				
				$this->_oView->init();
			}
			
			$this->runAfterMethod();
		} else {
			$this->runAfterMethod();

			// 404 // Method not found
			$this->_oRenderer->assign('content', '404');
		}

		// print debug info
		$this->_oRenderer->assign('aLogs', Debug::getLogs());

		$this->_oRenderer->assign('ctrl', strip_tags($_GET['ctrl']));
		$this->_oRenderer->assign('act', strip_tags($_GET['act']));
		
		$this->_oRenderer->display('layout.tpl');
	}

	// TODO clean... maybe refactor
	public function actionForward($sAction, $sCtrl = null, $bDieAfterForward = false) {
		$sCtrl = is_null($sCtrl) ? ucfirst($_GET['ctrl']) : $sCtrl;
		$sCtrlName = $sCtrl.'Controller';
		if (file_exists(CTRL_DIR.DS.$sCtrlName.'.php')) {
			require_once CTRL_DIR.DS.$sCtrlName.'.php';
			$oCtrl = new $sCtrlName;
			
			$oCtrl->_oRenderer = $this->_oRenderer;
		
			$sMethodName = $sAction.'Action';
			if (method_exists($oCtrl, $sMethodName)) {
				$oCtrl->$sMethodName();
				
				// view including
				$sViewName = $sCtrl.ucfirst($sAction).'View';
				if (file_exists(VIEW_DIR.DS.$sViewName.'.php')) {
					require_once VIEW_DIR.DS.$sViewName.'.php';
					$this->_oView = new $sViewName($this->_oRenderer);
					
					$this->_oView->init();
				}
			
				$this->runAfterMethod();
			}
		}
		if ($bDieAfterForward) {
			die();
		}
	}

	protected function _afterInit() {}
	
	public function runAfterMethod() {
		// wskazanie na  szablon widoku
		if ($this->_sTemplateName) {
			$this->_oRenderer->assign('content', $this->_sTemplateName);
			//$this->_oRenderer->assign('content', $this->getControllerName('lower').'-'.$this->getActionName('lower'));
		} else {
			$this->_oRenderer->assign('content', '404');
		}
	}

	public function auth() {
		if (isset($_POST['auth'])) {
			if (($_POST['auth']['user'] == 'ash' && $_POST['auth']['pass'] == 'demo10')
			|| ($_POST['auth']['user'] == 'pawel' && $_POST['auth']['pass'] == 'demo12')) {
				$_SESSION['user']['id'] = 1;
				$_SESSION['user']['active'] = 1;
				$_SESSION['user']['name'] = $_POST['auth']['user'];
			} else {
				$sAuthError = 'Nieprawidłowe dane podczas logowania';
				$this->_oRenderer->assign('sAuthError', $sAuthError);
			}
		} else {
			// echo 'WRONG';
		}

		// end when unauthorized
		if (!isset($_SESSION['user'])) {
			$this->_oRenderer->display('login.tpl');
			die();
		}
	}
}