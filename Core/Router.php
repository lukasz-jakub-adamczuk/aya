<?php

class Router {

	/**
	 * zwraca obiekt kontrolera
	 * 
	 * @return Controller $oController
	 */
	public static function init() {
		// sprawdzenie urla
		// TODO verifiy inputs
		if (isset($_GET['ctrl'])) {
			$sController = $_GET['ctrl'];
		} else {
			$sController = IVY_DEFAULT_CONTROLLER;
		}
		if (isset($_GET['act'])) {
			$sAction = $_GET['act'];
		} else {
			$sAction = IVY_DEFAULT_ACTION;
		}
		// echo $sController;
		// echo $sAction;

		$sControllerName = str_replace(' ', '', ucwords(str_replace('-', ' ', $sController))).'Controller';
		
		if (file_exists(CTRL_DIR.DS.$sControllerName.'.php')) {
			require_once CTRL_DIR.DS.$sControllerName.'.php';
			$oController = new $sControllerName;
			
			$oController->setActionName(ucfirst($sAction)).'Action';

			// echo $oController->getCtrlName('lower').'-'.$oController->getActionName('lower');
			// echo TPL_DIR;
			// echo TPL_DIR.DS.THEME_DIR.DS.$oController->getCtrlName('lower').'-'.$oController->getActionName('lower').'.tpl';
			// echo TPL_DIR.THEME_DIR.DS.$oController->getCtrlName('lower').'-'.$oController->getActionName('lower').'.tpl';
			
			$oController->setModelName($oController->getCtrlName().$oController->getActionName());
			$oController->setViewName($oController->getCtrlName().$oController->getActionName());
			if (file_exists(TPL_DIR.THEME_DIR.DS.$oController->getCtrlName('lower').'-'.$oController->getActionName('lower').'.tpl')) {
				$oController->setTemplateName($oController->getCtrlName('lower').'-'.$oController->getActionName('lower'));
			} else {
				if (file_exists(TPL_DIR.THEME_DIR.'/all-'.$oController->getActionName('lower').'.tpl')) {
					$oController->setTemplateName('all-'.$oController->getActionName('lower'));
				} else {
					// $oController->setTemplateName('all-index');
					$oController->setTemplateName('index');
				}
			}
		} else {
			if (file_exists(CTRL_DIR.'/ErrorController.php')) {
				require_once CTRL_DIR.'/ErrorController.php';
			} else {
				require_once AYA_DIR.'/Core/ErrorController.php';
			}
			$oController = new ErrorController;
		}
		//echo $oController;

		$oController->run();
	}
}