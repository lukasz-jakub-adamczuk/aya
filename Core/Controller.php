<?php
require_once SMARTY_DIR.'/Smarty.class.php';

abstract class Controller {

	/**
	 * parametry polaczenia z baza danych
	 * 
	 * @var unknown_type
	 */
	protected $_aParams;

	/**
	 * nazwa kontrolera
	 * 
	 * @var unknown_type
	 */
	protected $_sCtrlName;
	
	/**
	 * nazwa akcji
	 * 
	 * @var unknown_type
	 */
	protected $_sActionName;
	
	/**
	 * nazwa modelu
	 * 
	 * @var unknown_type
	 */
	protected $_sModelName;
	
	/**
	 * nazwa widoku
	 * 
	 * @var unknown_type
	 */
	protected $_sViewName;
	
	/**
	 * nazwa szablonu
	 * 
	 * @var unknown_type
	 */
	protected $_sTemplateName;
	
	/**
	 * obiekt polacznia z baza danych
	 * 
	 * @var unknown_type
	 */
	protected $_db;
	
	/**
	 * obiekt wersji jezykowej
	 * 
	 * @var unknown_type
	 */
	protected $_oLang;
	//protected $_session;
	
	/**
	 * obiekt modelu
	 * 
	 * @var unknown_type
	 */
	protected $_oModel;
	
	/**
	 * obiekt widoku
	 * 
	 * @var unknown_type
	 */
	protected $_oView;
	
	/**
	 * obiekt renderujacy szablony 
	 * 
	 * @var unknown_type
	 */
	protected $_oRenderer;

	/**
	 * ustawia nazwe kontrolera
	 * 
	 * @return unknown_type
	 */
	public function __construct() {
		//$this->_sCtrlName = str_replace('Controller', '', get_class($this));
		$this->_sCtrlName = str_replace('Controller', '', get_class($this));
	}

	/**
	 * zwraca nazwe kontrolera
	 * domyslnie 'caps' zwraca jako kapitaliki
	 * 'lower' jako male litery
	 * 
	 * @param $sCase format nazwy 
	 * @return unknown_type
	 */
	public function getCtrlName($sCase = 'caps') {
		if ($sCase == 'caps') {
			return $this->_sCtrlName;
		} elseif ($sCase == 'lower') {
			return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $this->_sCtrlName));
		} else {
			return $this->_sCtrlName;
		}
	}
	
	/**
	 * zwraca nazwe akcji
	 * domyslnie 'caps' zwraca jako kapitaliki
	 * 'lower' jako male litery
	 * 
	 * @param $sCase format nazwy 
	 * @return unknown_type
	 */
	public function getActionName($sCase = 'caps') {
		if ($sCase == 'caps') {
			return ucfirst($this->_sActionName);
		} elseif ($sCase == 'lower') {
			return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $this->_sActionName));
		} else {
			return $this->_sActionName;
		}
	}
	
	/**
	 * zwraca nazwe modelu
	 * 
	 * @return unknown_type
	 */
	public function getModelName() {
		return $this->_sModelName;
	}
	
	/**
	 * zwraca nazwe widoku
	 * 
	 * @return unknown_type
	 */
	public function getViewName() {
		return $this->_sViewName;
	}
	
	/**
	 * ustawia nazwe akcji
	 * 
	 * @param $sActionName
	 * @return unknown_type
	 */
	public function setActionName($sActionName) {
		$this->_sActionName = $sActionName;
	}
	
	/**
	 * ustawia nazwe modelu
	 * 
	 * @param $sModelName
	 * @return unknown_type
	 */
	public function setModelName($sModelName) {
		$this->_sModelName = $sModelName;
	}
	
	/**
	 * ustawia nazwe widoku
	 * 
	 * @param $sViewName
	 * @return unknown_type
	 */
	public function setViewName($sViewName) {
		$this->_sViewName = $sViewName;
	}
	
	/**
	 * ustawia nazwe szablonu
	 * 
	 * @param $sTemplateName
	 * @return unknown_type
	 */
	public function setTemplateName($sTemplateName) {
		$this->_sTemplateName = $sTemplateName;
	}
	
	/**
	 * ustawia obiekt widoku
	 * 
	 * @param $oView
	 * @return unknown_type
	 */
	public function setView(View $oView) {
		$this->_oView = $oView;
	}
	
	/**
	 * ustawia obiekt modelu
	 * 
	 * @param $oModel
	 * @return unknown_type
	 */
	public function setModel(Model $oModel) {
		$this->_oModel = $oModel;
	}
	
	public function actionForward($sAction, $sCtrl = null, $bDieAfterForward = false) {
//		echo 'ACTION_FORWARD';
        // TODO transform
        $sCtrl = is_null($sCtrl) ? $_GET['ctrl'] : $sCtrl;
		$sCtrlName = $sCtrl.'Controller';
		if (file_exists(CTRL_DIR.DS.$sCtrlName.'.php')) {
			require_once CTRL_DIR.DS.$sCtrlName.'.php';
			$oCtrl = new $sCtrlName;
	//		echo 'CTRL EXISTS';
			
			$oCtrl->_oRenderer = $this->_oRenderer;
		
			$sActionName = $sAction.'Action';
			if (method_exists($oCtrl, $sActionName)) {
	//			$this->setTemplateName($sTplName);
//	Router::debug($oController);
				$oCtrl->$sActionName();
				
//				echo 'ACTION EXISTS';
				
				// view including
				$sViewName = $sCtrl.ucfirst($sAction).'View';
				if (file_exists(VIEW_DIR.DS.$sViewName.'.php')) {
					require_once VIEW_DIR.DS.$sViewName.'.php';
					$this->_oView = new $sViewName;
					
		//			echo 'VIEW EXISTS';
					
					$this->_oView->init($this->_oRenderer);
					$this->_oView->fill();
				}
			
				$this->runAfterMethod();
			}
		}
		if ($bDieAfterForward) {
			die();
		}
	}
	
	// dla 
	protected function _init() {
	}

	/**
	 * podstawowe dzialania kontrolera
	 * 
	 * @param $params parematry konfiguracyjne dla obiektu Db
	 * @return unknown_type
	 */
	public function run() {
	
		$this->_aParams = unserialize(DB_SOURCE);

		$this->_db = Db::getInstance($this->_aParams);


		$this->_oRenderer = new Smarty;
		// $this->_oRenderer->template_dir = TPL_DIR.THEME_DIR;
		// $this->_oRenderer->compile_dir = TPL_C_DIR.THEME_DIR;
//		$this->_oRenderer->compile_check = true;



        $this->_oRenderer->setTemplateDir(TPL_DIR.THEME_DIR);
        $this->_oRenderer->setCompileDir(TPL_C_DIR.THEME_DIR);
//        $this->setConfigDir(GUESTBOOK_DIR . 'configs');
  //      $this->setCacheDir(GUESTBOOK_DIR . 'cache');
  
        // init kontorlera
        if (!isset($_SESSION['user'])) {
        	// echo 'unauthorized';
        	// $this->_a
        	// $this->_oRenderer->display('auth.tpl');
        	// $this->actionForward('login', 'auth');
        	

        	// $this->auth();

        	// die();	
        	// echo $_SESSION['auth'];
    	}
    	// print_r($_SESSION['user']);

        $this->_init();
		
		if (method_exists($this, $this->_sActionName.'Action')) {
			// rozpoznanie akcji wewnatrznych i sekcji
			if (isset($_POST['act'])) {
//				echo 'akcja wewnetrzna';
				$sAction = current(array_keys($_POST['act']));
				if (isset($_GET['action']) && $_GET['action'] !== 'index') {
					$sActionName = $sAction.ucfirst($_GET['action']).'Action';
					$sTplName = $this->getControllerName('lower').'-'.$_GET['action'].'-'.$sAction;
				} else {
					$sActionName = $sAction.'Action';
					$sTplName = $this->getControllerName('lower').'-'.$sAction;
				}
	
				if (method_exists($this, $sActionName)) {
					$this->setTemplateName($sTplName);
					$this->$sActionName();
				}
			} elseif (isset($_GET['section'])) {
//				echo 'sekcja';
				$sAction = $_GET['action'];
				$sSection = $_GET['section'];
				$sActionName = $sSection.ucfirst($sAction).'Action';
	
				if (method_exists($this, $sActionName)) {
					$this->setTemplateName($this->getControllerName('lower').'-'.$sAction.'-'.$sSection);
					$this->$sActionName();
				}
			} else {
//				echo 'akcja';
				$sMethodName = $this->_sActionName.'Action';
				$this->$sMethodName();
			}
			/*
			// model including
			$sModelName = $this->_sModelName.'Model';
			if (file_exists(MODEL_DIR.$sModelName.'.php')) {
				require_once MODEL_DIR.$sModelName.'.php';
				$this->_oModel = new $sModelName;
				
				$this->_oModel->init($this->_oRenderer);
				$this->_oModel->perform();
			}*/

			// view including
			$sViewName = $this->_sViewName.'View';
			//echo VIEW_DIR.$sViewName.'.php';
			if (file_exists(VIEW_DIR.'/'.$sViewName.'.php')) {
				require_once VIEW_DIR.'/'.$sViewName.'.php';
				$this->_oView = new $sViewName;
				
				$this->_oView->init($this->_oRenderer);
				$this->_oView->fill();
			}
		    
			$this->runAfterMethod();
		} else {
			$this->runAfterMethod();

			// 404 // Method not found
			$this->_oRenderer->assign('content', '404');
			//$this->_oRenderer->assign('content', 'offer-index');
			
			// $this->_oRenderer->assign('sLocalUrl', LOCAL_URL);
		}

		// echo 'GET[CTRL]: '.$_GET['ctrl'].', ';
		// echo 'GET[ACT]: '.$_GET['act'].', ';

		// echo 'CTRL: '.$this->_sCtrlName.', ';
		// echo 'ACT: '.$this->_sActionName.', ';

		// echo 'END FLOW';
		
		//$this->_oRenderer->display('index.tpl');
		$this->_oRenderer->display('layout.tpl');
	}
	
	/**
	 * dodatkowe dzialania kontrolera
	 * 
	 * @return unknown_type
	 */
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