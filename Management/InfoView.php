<?php
require_once AYA_DIR.'/Core/View.php';
require_once AYA_DIR.'/Html/Form/HtmlForm.php';

class InfoView extends View {

	public function configureForm(&$oForm) {
		// inheritance
	}

	public function postProcessDataset($aFields) {
		return $aFields;
	}
	
	public function fill() {
		// entity
		if (isset($_GET['id']) || isset($_POST['id'])) {
			//$iId = isset($_GET['id']) ? intval($_GET['id']) : intval($_POST['id']);
			$iId = intval($_REQUEST['id']);
			
			$oInstance = Dao::entity($this->_sDaoName, $iId, 'id_'.$this->_sDaoIndex);

			// echo 'id_'.$this->_sDaoIndex;

			$aFields = $this->postProcessDataset($oInstance->getFields());

			$this->_oRenderer->assign('aFields', $aFields);

			// $this->postPoccessFields($aFields);

			Debug::show($oInstance->getFields());
			
			$sFormMode = 'update';
		} else {
			$sFormMode = 'insert';

			// $aFields = array();
		}

		$this->_oRenderer->assign('sFormMode', $sFormMode);

		// TODO better ctrl name
		$this->_oRenderer->assign('sFormMainPartTemplate', 'forms/'.$_GET['ctrl'].'-info-main.tpl');
		$this->_oRenderer->assign('sFormSubPartTemplate', 'forms/'.$_GET['ctrl'].'-info-sub.tpl');

		
		// insert or update with errors
		if (isset($_POST['dataset'])) {
			$this->_oRenderer->assign('aFields', $_POST['dataset']);
		}

	}

	public function beforeFill() {
		// inheritance
		$this->_sOwner = $this->_sDaoName.'-'.$_GET['ctrl'].'-'.$_GET['act'];

		Navigator::setOwner($this->_sOwner);
	}

	public function afterFill() {
		// inheritance
	}
}