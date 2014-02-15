<?php
require_once AYA_DIR.'/Core/View.php';
require_once AYA_DIR.'/Html/HtmlForm.php';

class InfoView extends View {
	
	public function fill() {
		// startowe dzialanie
		$this->_runBeforeFill();
		
		// ewentualne szczegoly encji
		// if (isset($_GET['id']) || isset($_POST['id'])) {
		//     //$iId = isset($_GET['id']) ? intval($_GET['id']) : intval($_POST['id']);
		//     $iId = intval($_REQUEST['id']);
			
		//     // $oInstance = Dao::entity($this->_sDaoName, $iId, 'id_'.$this->_sDaoIndex);
			
		//     $sFormMode = 'update';
		// } else {
		//     $sFormMode = 'insert';
		// }

		echo 'INFO VIEW';


		$oCategories = Dao::collection('category');
		// $oCategories->select('SELECT * ');
		$oCategories->orderby('name');
		$oCategories->load(-1);

		// print_r($oCategories->getRows());

		$this->_oRenderer->assign('aCategories', $oCategories->getRows());

		
		$sId = str_replace('_', '-', $this->_sDaoIndex);
		
		// form
		$oForm = new HtmlForm($sId);
		
		// $oForm->configure($sFormMode);
		
		$oForm->setCacheDir(APP_DIR.'/tmp');
		
		// update
		// if ($sFormMode == 'update') {
		// 	$oForm->setFormValues(array('id' => $oInstance->getId()));
		
		// 	$oForm->setFormValues($oInstance->getFields());
		// }
		
		// insert or update with errors
		if (isset($_POST['dataset'])) {
			$oForm->setFormValues($_POST['dataset']);
		}
		
		$this->configureForm($oForm);
		
		//print_r($oForm);

		require_once __DIR__ . '/../../XhtmlTable/Aya/Yaml/AyaYamlLoader.php';
		
		// $sYamlFile = X_FORM_LANG_DIR.'/pl/forms/'.str_replace('_', '-', $this->_sDaoIndex).'.yaml';
		// if (file_exists($sYamlFile)) {
		// 	$aConfig = AyaYamlLoader::parse($sYamlFile);

		// 	$aFormTexts = $oYamlParser->parse(file_get_contents($sYamlFile));
		
		// 	$oForm->setFormTexts($aFormTexts);
		// }
		
		
		$this->_oRenderer->assign('sForm', $oForm->render());
	}
	
	public function configureForm(&$oForm) {
		// dla potomnych
	}
}