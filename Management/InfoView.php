<?php
require_once AYA_DIR.DS.'Core/View.php';
require_once AYA_DIR.DS.'Xhtml/Form/XhtmlForm.php';

class InfoView extends View {
	
	public function fill() {
		// startowe dzialanie
		$this->_runBeforeFill();
		
		// ewentualne szczegoly encji
		if (isset($_GET['id']) || isset($_POST['id'])) {
		    //$iId = isset($_GET['id']) ? intval($_GET['id']) : intval($_POST['id']);
		    $iId = intval($_REQUEST['id']);
		    
		    $oInstance = Dao::entity($this->_sDaoName, $iId, 'id_'.$this->_sDaoIndex);
		    
		    $sFormMode = 'update';
		} else {
		    $sFormMode = 'insert';
		}
		
		// form
		$oForm = new XhtmlForm(str_replace('_', '-', $this->_sDaoIndex));
		
		$oForm->configure($sFormMode);
		
		$oForm->setCacheDir(APP_DIR.'/tmp');
		
		// update
		if ($sFormMode == 'update') {
		    $oForm->setFormValues(array('id' => $oInstance->getId()));
		
		    $oForm->setFormValues($oInstance->getAllFields());
	    }
	    
	    // insert or update with errors
	    if (isset($_POST['dataset'])) {
	        $oForm->setFormValues($_POST['dataset']);
	    }
	    
	    $this->configureForm($oForm);
		
		//print_r($oForm);
		
		$sYamlFile = X_FORM_LANG_DIR.'/pl/forms/'.str_replace('_', '-', $this->_sDaoIndex).'.yaml';
		$oYamlParser = new sfYamlParser();
        $aFormTexts = $oYamlParser->parse(file_get_contents($sYamlFile));
		
		$oForm->setFormTexts($aFormTexts);
		
		
		$this->_oRenderer->assign('sForm', $oForm->render());
		
	}
	
	public function configureForm(&$oForm) {
	    // dla potomnych
	}
}
?>
