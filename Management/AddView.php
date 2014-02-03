<?php
require_once AYA_DIR.DS.'Core/View.php';
require_once AYA_DIR.DS.'Xhtml/Form/XhtmlForm.php';

class AddView extends View {
	
	public function fill() {
		// startowe dzialanie
		$this->_runBeforeFill();
		
		// config
		//echo $this->_sActionName;
		//echo $this->_sViewName;
		$sCtrlName = str_replace($this->_sActionName, '', $this->_sViewName);
		echo $sDaoIndex = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $sCtrlName));
		
		// ewentualne szczegoly encji
		if (isset($_GET['id'])) {
		    $iId = intval($_GET['id']);
		    
		    $oInstance = Dao::entity($sCtrlName, $iId, 'id_'.$sDaoIndex);
		    
		    $aValues = $oInstance->getAllFields();
		    $sFormMode = 'update';
		} else {
		    $sFormMode = 'insert';
		}
		
		// form
		$oForm = new XhtmlForm(str_replace('_', '-', $this->_sDaoIndex));

		// echo $sDaoIndex;
		// print_r($oForm);
		
		$oForm->configure($sFormMode);
		
		$oForm->setCacheDir(APP_DIR.'/tmp');
		
		if ($sFormMode == 'update') {
		    $oForm->setFormValues(array('id' => $oInstance->getId()));
		
		    $oForm->setFormValues($oInstance->getAllFields());
	    }

	    $this->configureForm($oForm);

	    $sYamlFile = X_FORM_LANG_DIR.'/pl/forms/'.str_replace('_', '-', $this->_sDaoIndex).'.yaml';
		$oYamlParser = new sfYamlParser();
        $aFormTexts = $oYamlParser->parse(file_get_contents($sYamlFile));
		
		$oForm->setFormTexts($aFormTexts);
		
//		print_r($oForm);
		
		
		$this->_oRenderer->assign('sForm', $oForm->render());
		
	}
}
?>
