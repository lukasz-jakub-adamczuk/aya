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

		
		Time::start('ctrl-init');
		$this->init();

		$this->_afterInit();
		Time::stop('ctrl-init');

		Debug::show($this->_sTemplateName, 'template before auth()');

		// auth or not ?
		if (AUTH_MODE) {
			if (isset($_SESSION['user'])) {
				$this->actionForward('index', 'auth');
			} else {
				$this->actionForward('index', 'auth', true);
			}
		}


		// TODO find better way to transform
		$sActionName = ucwords(str_replace('-', ' ', $this->_sActionName));

		$aParts = explode(' ', $sActionName);
		$aParts[0] = strtolower($aParts[0]);

		$sActionName = implode('', $aParts);


		$sMethodName = $sActionName.'Action';

		// echo $sMethodName;

		// controller action
		if (method_exists($this, $sMethodName)) {
			// Debug::show($this->getTemplateName(), 'tpl name in ctrl');
			$this->$sMethodName();
			// Debug::show($this->getTemplateName(), 'tpl name in ctrl');
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

		Debug::show($this->getViewName(), 'view name in init');

		

		// try '<ctrl_name>-<action_name>.tpl'
		$sTplName = $this->getCtrlName('ctrl', 'lower').'-'.$this->getName('action', 'lower');
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

	protected function _afterInit() {
		// Debug::show($this->getTemplateName(), 'tpl name in ctrl');
	}

	// TODO clean... maybe refactor
	public function actionForward($sAction, $sCtrl = null, $bOverrideTemplate = false, $bDieAfterForward = false) {
		Debug::show($this->_sTemplateName, 'template before actionForward()');
		$sCtrl = is_null($sCtrl) ? $_GET['ctrl'] : $sCtrl;

		$sCtrlName = ucfirst($sCtrl).'Controller';
		Debug::show($sCtrlName, 'ctrl in actionForward()');
		if (file_exists(CTRL_DIR.DS.$sCtrlName.'.php')) {
			require_once CTRL_DIR.DS.$sCtrlName.'.php';
			$oCtrl = new $sCtrlName;

			$oCtrl->setCtrlName($sCtrl);
			$oCtrl->setActionName($sAction);

			$oCtrl->init();
			
			$oCtrl->_oRenderer = $this->_oRenderer;

		
			$sMethodName = $sAction.'Action';
			Debug::show($sMethodName, 'action in actionForward()');

			Debug::show($this->getTemplateName(), 'template in actionForward() $this ctrl');
			Debug::show($oCtrl->getTemplateName(), 'template in actionForward() $oCtrl ctrl');
			if (method_exists($oCtrl, $sMethodName)) {
				

				$oCtrl->$sMethodName();

				if ($bOverrideTemplate) {
					// set template name to parent initial ctrl
					$this->setTemplateName($oCtrl->getTemplateName());
				}
				
				// view including
				$sViewName = $sCtrl.ucfirst($sAction).'View';
				if (file_exists(VIEW_DIR.DS.$sViewName.'.php')) {
					require_once VIEW_DIR.DS.$sViewName.'.php';
					$oCtrl->_oView = new $sViewName($this->_oRenderer);
					
					$oCtrl->_oView->init();
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
		Debug::show($this->_sTemplateName, 'runAfterMethod() in ' . $this->getCtrlName() . ' ctrl');
		if ($this->_sTemplateName) {
			$this->_oRenderer->assign('content', $this->_sTemplateName);
		} else {
			$this->_oRenderer->assign('content', '404');
		}
	}
}