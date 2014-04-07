<?php
require_once AYA_DIR.'/Management/FrontController.php';

class CrudController extends FrontController {
	
	public function indexAction() {

		// decide what to do with action
		if (isset($_POST['action'])) {
			$sAction = key($_POST['action']).'Action';
			// unset($_POST['action']);
			// echo 'MASS in indexAction();';

			if (isset($_POST['ids'])) {
				$this->$sAction();
			}
		}
	}
	
	public function infoAction() {
	}
	
	public function editAction() {
	
	}
	
	public function addAction() {
		$this->actionForward('info');
	}
	
	public function insertAction() {
		// $iId = isset($_POST['id']) ?;
		$iId = 0;
		
		$oEntity = Dao::entity($this->_sCtrlName, $iId);
		
		$oEntity->setFields($_POST['dataset']);

		$oEntity->setField('slug', $this->slugify($_POST['dataset']['title']));

		// $oEntity->setField('creation_date', '2014-02-21 18:30:00');
		$oEntity->setField('creation_date', date('Y-m-d H:i:s'));
		// $oEntity->setField('creation_date', 'NOW()');



		$sTitle = $_POST['dataset']['title'];
		
		//print_r($oEntity);
		
		if ($iId = $oEntity->insert()) {
			// clear cache
			$sSqlCacheFile = TMP_DIR . '/sql/collection/'.$this->_sCtrlName.'-'.$this->_sActionName.'';

			$aMsg['text'] = 'Wpis <strong>'.$sTitle.'</strong> został utworzony.';
			$aMsg['type'] = 'info';

			$this->addHistoryLog('create', $this->_sCtrlName, $iId);

			$this->actionForward('index', $this->_sCtrlName, true);
		} else {
			$aMsg['text'] = 'Wystąpił nieoczekiwany wyjątek.';
			$aMsg['type'] = 'error';
			$this->actionForward('info', $this->_sCtrlName);
		}
		$this->_oRenderer->assign('aMsgs', array($aMsg));
	}
	
	public function updateAction() {
		$iId = $_POST['id'];
		
		$oEntity = Dao::entity($this->_sCtrlName, $iId, 'id_'.$this->_sCtrlName);
		
		$oEntity->setFields($_POST['dataset']);

		$oEntity->setField('slug', $this->slugify($_POST['dataset']['title']));

		$oEntity->setField('modification_date', date('Y-m-d H:i:s'));

		// old title for message
		$sTitle = $oEntity->getField('title');

		echo $sSqlCacheFile = TMP_DIR . '/sql/collection/'.$this->_sCtrlName.'-'.$this->_sActionName.'';
		
		if ($oEntity->update()) {
			$sEditUrl = BASE_URL.'/'.$this->_sCtrlName.'/'.$iId;
			$aMsg['text'] = 'Wpis <strong>'.$sTitle.'</strong> został zmieniony. <a href="'.$sEditUrl.'">Edytuj</a> ponownie.';
			$aMsg['type'] = 'info';

			$this->addHistoryLog('update', $this->_sCtrlName, $iId);
			
			$this->actionForward('index', $this->_sCtrlName, true);
		} else {
			$aMsg['text'] = 'Wystąpił nieoczekiwany wyjątek.';
			$aMsg['type'] = 'error';
			$this->actionForward('info', $this->_sCtrlName);
		}
		$this->_oRenderer->assign('aMsgs', array($aMsg));
	}

	public function deleteAction() {
		if (isset($_POST['ids'])) {
			$aIds = $_POST['ids'];
			// print_r($aIds);
		}
		
		if (isset($aIds)) {
			$aTitles = array();
			foreach ($aIds as $id) {
				$oEntity = Dao::entity($this->_sCtrlName, $id, 'id_'.$this->_sCtrlName);

				// if ($oInstance->hasField('title')) {
				$sTitle = $oEntity->getField('title');
				// }

				$oEntity->setField('deleted', '1');
				
				if ($oEntity->update()) {
				// if (true) {
					// echo 'DELETE ACTION...';
					$this->addHistoryLog('delete', $this->_sCtrlName, $id);
					// print_r($oInstance);
					$aTitles[] = $sTitle;   
					// $this->actionForward('index', $this->_sCtrlName);
				}
			}

			// msg
			if (count($aTitles) == 1) {
				$aMsg['text'] = 'Wpis <strong>'.$aTitles[0].'</strong> został usunięty.';
				$aMsg['type'] = 'info';
			} elseif (count($aTitles) > 1) {
				$aMsg['text'] = 'Wpisy <strong>'.implode(', ', $aTitles).'</strong> zostały usunięte.';
				$aMsg['type'] = 'info';
			} else {
				$aMsg['text'] = 'Wystąpił nieoczekiwany wyjątek.';
				$aMsg['type'] = 'warning';
			}
			$this->_oRenderer->assign('aMsgs', array($aMsg));
		}
	}

	public function addHistoryLog($sActionType, $sTableName, $iId, $sLog = '') {
		$oEntity = Dao::entity('history_log');

		$oEntity->setField('id_author', $_SESSION['user']['id']);
		$oEntity->setField('id_record', $iId);
		$oEntity->setField('table', $sTableName);
		$oEntity->setField('log', $sLog);
		$oEntity->setField('creation_date', date('Y-m-d H:i:s'));
		$oEntity->setField('type', $sActionType);

		$oEntity->insert();
	}

	
	private function slugify($text) { 
		// replace non letter or digits by -
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text);
		
		// trim
		$text = trim($text, '-');
		
		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		
		// lowercase
		$text = strtolower($text);
		
		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);
		
		if (empty($text)) {
			return 'n-a';
		}
		
		return $text;
	}
}