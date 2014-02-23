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

	public function getTemplateName() {
		return $this->_sTemplateName;
	}

	public function run() {
		// methods executed inside
		// init()
		// _afterInit()
		Debug::show('flow begins...');

		Time::start('controller');

		// DB params serialized in constant
		$this->_aParams = unserialize(DB_SOURCE);

		// DB handle
		$this->_db = Db::getInstance($this->_aParams);

		// session init
		session_start();

		// session_unset($_SESSION['user']);

		// template engine
		$this->_oRenderer = new Smarty;

		Debug::show(TPL_DIR.THEME_DIR, 'template_dir');
		Debug::show(TPL_C_DIR.THEME_DIR, 'template_c_dir');

		$this->_oRenderer->setTemplateDir(TPL_DIR.THEME_DIR);
		$this->_oRenderer->setCompileDir(TPL_C_DIR.THEME_DIR);
		// $this->setConfigDir(GUESTBOOK_DIR . 'configs');
		// $this->setCacheDir(GUESTBOOK_DIR . 'cache');
  
		// auth or not ?
		if (!isset($_SESSION['user'])) {
			Debug::show('unauthorized', 'user', 'warning');
			// $this->_a
			// $this->_oRenderer->display('auth.tpl');
			// $this->actionForward('login', 'auth');
			

			// $this->auth();

			// die();	
			// echo $_SESSION['auth'];
		} else {
			Debug::show('authorized', 'user', 'info');
		}
		Time::start('ctrl-init');
		$this->init();

		$this->_afterInit();
		Time::stop('ctrl-init');

		// TODO find better way to transform
		$sActionName = ucwords(str_replace('-', ' ', $this->_sActionName));

		$aParts = explode(' ', $sActionName);
		$aParts[0] = strtolower($aParts[0]);

		$sActionName = implode('', $aParts);


		$sMethodName = $sActionName.'Action';

		// controller action
		if (method_exists($this, $sMethodName)) {
			$this->$sMethodName();
			Time::stop('ctrl-method');

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
		Time::stop('ctrl-action');

		$this->_oRenderer->assign('ctrl', $this->_sCtrlName);
		$this->_oRenderer->assign('act', $this->_sActionName);

		Debug::show('flow ends...');

		Time::stop('controller');

		Time::total(true);

		Debug::show(Time::stats(), 'Time stats');
		
		// assign debug info
		$this->_oRenderer->assign('aLogs', Debug::getLogs());

		$this->_oRenderer->display('layout.tpl');
	}

	public function init() {
		$this->setViewName($this->getName('ctrl').$this->getName('action'));

		// try '<ctrl_name>-<action_name>.tpl'
		$sTplName = $this->getCtrlName().'-'.$this->getActionName();
		if (!file_exists(TPL_DIR.THEME_DIR.DS.$sTplName.'.tpl')) {
			// try 'all-<action_name>.tpl'
			$sTplName = 'all-'.$this->getActionName();
			if (!file_exists(TPL_DIR.THEME_DIR.DS.$sTplName.'.tpl')) {
				// try index.tpl
				$sTplName = 'index';
			}
		}
		$this->setTemplateName($sTplName);

		Debug::show($sTplName, 'template init');
	}

	protected function _afterInit() {}

	// TODO clean... maybe refactor
	public function actionForward($sAction, $sCtrl = null, $bDieAfterForward = false) {
		$sCtrl = is_null($sCtrl) ? $_GET['ctrl'] : $sCtrl;

		$sCtrlName = ucfirst($sCtrl).'Controller';
		Debug::show($sCtrlName, 'ctrl in actionForward()');
		if (file_exists(CTRL_DIR.DS.$sCtrlName.'.php')) {
			require_once CTRL_DIR.DS.$sCtrlName.'.php';
			$oCtrl = new $sCtrlName;

			$oCtrl->setCtrlName($sCtrl);
			$oCtrl->setActionName($sAction);
			
			$oCtrl->_oRenderer = $this->_oRenderer;
		
			$sMethodName = $sAction.'Action';
			Debug::show($sMethodName, 'action in actionForward()');
			if (method_exists($oCtrl, $sMethodName)) {
				Debug::show($this->getTemplateName(), 'before $this->action()', 'info');

				$oCtrl->init();

				$oCtrl->$sMethodName();

				// set template name to parent initial ctrl
				$this->setTemplateName($oCtrl->getTemplateName());
				
				// view including
				$sViewName = $sCtrl.ucfirst($sAction).'View';
				if (file_exists(VIEW_DIR.DS.$sViewName.'.php')) {
					require_once VIEW_DIR.DS.$sViewName.'.php';
					$oCtrl->_oView = new $sViewName($this->_oRenderer);
					
					$oCtrl->_oView->init();
				}
			
				$oCtrl->runAfterMethod('actionForward');

				
			}
		}
		if ($bDieAfterForward) {
			die();
		}
	}
	
	public function runAfterMethod($sName = '') {
		// wskazanie na  szablon widoku
		Debug::show($this->_sTemplateName, 'runAfterMethod in... ' . $sName);
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