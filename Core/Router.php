<?php

class Router {

	public static function init() {
		// sprawdzenie urla
		if (isset($_GET['ctrl'])) {
			$sController = strip_tags($_GET['ctrl']);
		} else {
			$sController = DEFAULT_CONTROLLER;
		}
		if (isset($_GET['act'])) {
			$sAction = strip_tags($_GET['act']);
		} else {
			$sAction = DEFAULT_ACTION;
		}

		Debug::show($sController);
		Debug::show($sAction);

		$sControllerName = str_replace(' ', '', ucwords(str_replace('-', ' ', $sController))).'Controller';
		
		if (file_exists(CTRL_DIR.DS.$sControllerName.'.php')) {
			require_once CTRL_DIR.DS.$sControllerName.'.php';
			$oController = new $sControllerName;
			
			$oController->setCtrlName($sController);
			$oController->setActionName($sAction);
		} else {
			if (file_exists(CTRL_DIR.'/ErrorController.php')) {
				require_once CTRL_DIR.'/ErrorController.php';
			} else {
				require_once AYA_DIR.'/Core/ErrorController.php';
			}
			$oController = new ErrorController;
		}

		$oController->run();
	}
}