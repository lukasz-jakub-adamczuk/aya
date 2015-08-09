<?php

abstract class Controller {

	protected $_sContentType;

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

	public function setContentType($sContentType) {
		$this->_sContentType = $sContentType;
	}
	
	public function setCtrlName($sCtrlName) {
		$this->_sCtrlName = $sCtrlName;
	}

	public function setActionName($sActionName) {
		$this->_sActionName = $sActionName;
	}
	
	public function setViewName($sViewName) {
		$this->_sViewName = $sViewName;
	}
	
	public function setTemplateName($sTplName) {
		// if (file_exists(TPL_DIR.THEME_DIR.DS.$sTplName.'.tpl')) {
			$this->_sTemplateName = $sTplName;
		// } else {
		// 	$this->_sTemplateName = '404';
		// }
	}
	
	public function setView(View $oView) {
		$this->_oView = $oView;
	}

	// getters

	public function getContentType() {
		return $this->_sContentType;
	}

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

		$this->_sContentType = 'html';

		// DB params serialized in constant
		$this->_aParams = unserialize(DB_SOURCE);

		Time::start('db-init');
		// DB handle
		$this->_db = Db::getInstance($this->_aParams);
		Time::stop('db-init');

		// session init
		session_start();

		// create container
		User::instance();

		// session_unset($_SESSION['user']);
		
		// template engine
		require_once TPL_ENGINE_DIR.'/libs/Smarty.class.php';
		
		$this->_oRenderer = new Smarty;

		// $this->_oRenderer->caching = 2;
		// $this->_oRenderer->cache_lifetime = 60;
		// $this->_oRenderer->compile_check = false;

		Debug::show(TPL_DIR.THEME_DIR, 'template_dir');
		Debug::show(TPL_C_DIR.THEME_DIR, 'template_c_dir');

		$this->_oRenderer->setTemplateDir(TPL_DIR.THEME_DIR);
		$this->_oRenderer->setCompileDir(TPL_C_DIR.THEME_DIR);
		$this->_oRenderer->setConfigDir(APP_DIR.'/configs');
		// $this->_oRenderer->setCacheDir(APP_DIR.'/cache');
		// $this->setCacheDir(GUESTBOOK_DIR . 'cache');
		Time::stop('smarty-init');

		
		Time::start('ctrl-init');
		$this->init();

		// could be use for authentication
		$this->_afterInit();
		
		Time::stop('ctrl-init');

		// Debug::show($this->_sTemplateName, 'template before auth()');

		Debug::show($this->getCtrlName(), 'ctrl sent to templates in ctrl');
		Debug::show($this->getActionName(), 'ctrl sent to templates in ctrl');
		$this->_oRenderer->assign('ctrl', $this->getCtrlName());
		$this->_oRenderer->assign('act', $this->getActionName());

		


		// TODO find better way to transform
		$sActionName = ucwords(str_replace('-', ' ', $this->_sActionName));

		$aParts = explode(' ', $sActionName);
		$aParts[0] = strtolower($aParts[0]);

		$sActionName = implode('', $aParts);


		$sMethodName = $sActionName.'Action';

		// echo $sMethodName;
		$sCacheString = CACHE_DIR.str_replace(BASE_URL, '', $_SERVER['REQUEST_URI']).'-file';
		// print_r($_SERVER);
		// echo BASE_URL;
		if (CACHE_OUTPUT && file_exists($sCacheString)) {
			$this->_oRenderer->display($sCacheString);
			// echo file_get_contents($sCacheString);
		} else {
			// controller action
			if (method_exists($this, $sMethodName)) {
				$this->runBeforeMethod();

				// Debug::show($this->getTemplateName(), 'tpl name in ctrl');
				$this->$sMethodName();
				// Debug::show($this->getTemplateName(), 'tpl name in ctrl');
				Time::stop('ctrl-method');

				// including view for action
				$sViewName = $this->_sViewName.'View';
				
				if (file_exists(VIEW_DIR.'/'.$sViewName.'.php')) {
					require_once VIEW_DIR.'/'.$sViewName.'.php';
					
					$this->_oView = new $sViewName($this->_oRenderer);
					
					$this->_oView->run();
				}
				$this->runAfterMethod();

				// $this->_oRenderer->assign('ctrl', $this->getCtrlName());
				// $this->_oRenderer->assign('act', $this->getActionName());
			} else {
				$this->runAfterMethod();

				// 404 // Method not found
				$this->_oRenderer->assign('content', '404');
			}
			Time::stop('ctrl-action');

			// $this->_oRenderer->assign('ctrl', $this->_sCtrlName);
			// $this->_oRenderer->assign('act', $this->_sActionName);

			Debug::show('flow ends...');

			Time::stop('controller');

			Time::total(true);

			Debug::show(Time::stats(), 'Time stats');

			Time::stop();
			$this->_oRenderer->assign('sServerTime', Time::get());
			
			// assign debug info
			$this->_oRenderer->assign('aLogs', Debug::getLogs());

			// assign messages
			$this->_oRenderer->assign('aMsgs', MessageList::get());

			// print_r(Debug::getLogs());

			if ($this->_sContentType != 'html') {
				$sOutput = $this->_oRenderer->fetch('layout.'.$this->_sContentType.'.tpl');
			} else {
				$sOutput = $this->_oRenderer->fetch('layout.tpl');
			}
			echo $sOutput;

			if (CACHE_OUTPUT) {
				$sCacheDir = dirname($sCacheString);
				if (!file_exists($sCacheDir)) {
					mkdir($sCacheDir, 0777, true);
				}
				file_put_contents($sCacheString, $sOutput);
			}
		}
	}

	public function init() {
		$this->setViewName($this->getName('ctrl').$this->getName('action'));

		Debug::show($this->getViewName(), 'view name in init');

		

		// try '<ctrl_name>-<action_name>.tpl'
		$sTplName = $this->getCtrlName('ctrl', 'lower').'-'.$this->getName('action', 'lower');
		Debug::show($sTplName, '1. template init');
		if (!file_exists(TPL_DIR.THEME_DIR.DS.$sTplName.'.tpl')) {
			// try 'all-<action_name>.tpl'
			$sTplName = 'all-'.$this->getActionName();
			Debug::show($sTplName, '2. template init');
			if (!file_exists(TPL_DIR.THEME_DIR.DS.$sTplName.'.tpl')) {
				// try index.tpl
				$sTplName = 'index';
				Debug::show($sTplName, '3. template init');
			}
		}
		$this->setTemplateName($sTplName);

		
	}

	protected function _afterInit() {
		// Debug::show($this->getTemplateName(), 'tpl name in ctrl');
	}

	// TODO clean... maybe refactor
	public function actionForward($sAction, $sCtrl = null, $bOverrideTemplate = false, $aParams = null, $bDieAfterForward = false) {
		Debug::show($this->_sTemplateName, 'template before actionForward()');
		$sCtrl = is_null($sCtrl) ? $_GET['ctrl'] : $sCtrl;

		// set additional params
		if ($aParams) {
			foreach ($aParams as $pk => $param) {
				$aParts = explode(':', $pk);
				if ($aParts[0] == 'get') {
					$_GET[$aParts[1]] = $param;
				}
			}
		}
		// print_r($aParams);
		Debug::show($aParams, 'params from redirect action');
		Debug::show($_GET, 'params assigned to $_GET');

		$sCtrlName = str_replace(' ', '', ucwords(str_replace('-', ' ', $sCtrl))).'Controller';
		Debug::show($sCtrlName, 'ctrl in actionForward()');
		if (file_exists(CTRL_DIR.DS.$sCtrlName.'.php')) {
			require_once CTRL_DIR.DS.$sCtrlName.'.php';
			$oCtrl = new $sCtrlName;

			$oCtrl->setCtrlName($sCtrl);
			$oCtrl->setActionName($sAction);

			$oCtrl->init();
			
			$oCtrl->_oRenderer = $this->_oRenderer;

		
			$sMethodName = $sAction.'Action';
			Debug::show($sMethodName, 'method in actionForward()');

			Debug::show($this->getTemplateName(), 'template in actionForward() $this ctrl');
			Debug::show($oCtrl->getTemplateName(), 'template in actionForward() $oCtrl ctrl');
			Debug::show(array('ctrl' => $sCtrlName, 'method' => $sMethodName), 'ctrl and method in actionForward()');
			if (method_exists($oCtrl, $sMethodName)) {
				$oCtrl->runBeforeMethod();

				// action method in ctrl
				$oCtrl->$sMethodName();

				if ($bOverrideTemplate) {
					// set template name to parent initial ctrl
					$this->setTemplateName($oCtrl->getTemplateName());

					Debug::show($oCtrl->getCtrlName(), 'ctrl sent to tempplates in actionForward');
					Debug::show($oCtrl->getActionName(), 'ctrl sent to tempplates in actionForward');
					$this->_oRenderer->assign('ctrl', $oCtrl->getCtrlName());
					$this->_oRenderer->assign('act', $oCtrl->getActionName());
				}
				
				// view including
				$sCtrlName = str_replace(' ', '', ucwords(str_replace('-', ' ', $sCtrl)));
				$sActionName = str_replace(' ', '', ucwords(str_replace('-', ' ', $sAction)));
				$sViewName = $sCtrlName.$sActionName.'View';
				Debug::show($sViewName, 'view in actionForward()');
				if (file_exists(VIEW_DIR.DS.$sViewName.'.php')) {
					require_once VIEW_DIR.DS.$sViewName.'.php';
					$oCtrl->_oView = new $sViewName($this->_oRenderer);
					
					$oCtrl->_oView->run();
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