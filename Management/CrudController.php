<?php
require_once AYA_DIR.'/Management/FrontController.php';

class CrudController extends FrontController {
	
	public function indexAction() {
		// decide what to do with action
		if (isset($_POST['action'])) {
			$sAction = key($_POST['action']).'Action';
			// unset($_POST['action']);

			if (isset($_POST['ids'])) {
				$this->$sAction();
			}
		}
	}
	
	public function infoAction() {}
	
	public function addAction() {
		$this->setTemplateName($this->_sCtrlName.'-info');
		$this->setViewName(str_replace(' ', '', ucwords(str_replace('-', ' ', $this->_sCtrlName.'-info'))));
	}
	
	public function insertAction() {
		$iId = 0;

		$oEntity = Dao::entity($this->_sCtrlName, $iId);
		
		$oEntity->setFields($_POST['dataset']);

		$aPossibleNameKeys = array('title', 'name');
		foreach ($aPossibleNameKeys as $key) {
			if (isset($_POST['dataset'][$key])) {
				$sName = $_POST['dataset'][$key];
				break;
			}
		}

		// slug by used name if empty or changed name
		if (isset($sName) && isset($_POST['dataset']['slug']) && (empty($_POST['dataset']['slug']) || $_POST['dataset']['slug'] != $this->slugify($sName))) {
			$oEntity->setField('slug', $this->slugify($sName));
		}

		// no creation date
		// TODO or creation date invalid
		if (empty($_POST['dataset']['creation_date'])) {
			$oEntity->setField('creation_date', date('Y-m-d H:i:s'));
		}
		// if mod_date comes somehow
		if (empty($_POST['dataset']['modification_date'])) {
			$oEntity->unsetField('modification_date');
		}
		
		if ($iId = $oEntity->insert(true)) {
			$this->postInsert($iId);
			// clear cache
			$sSqlCacheFile = TMP_DIR . '/sql/collection/'.$this->_sCtrlName.'-'.$this->_sActionName.'';

			$this->raiseInfo('Wpis <strong>'.$sName.'</strong> został utworzony.');

			$this->addHistoryLog('create', $this->_sCtrlName, $iId);

			// $aStreamItem = $this->prepareStreamItem($iId, $_POST);
			// $this->addToStream($aStreamItem);

			$this->actionForward('index', $this->_sCtrlName, true);
		} else {
			$this->raiseError('Wystąpił nieoczekiwany wyjątek.');
			$this->actionForward('info', $this->_sCtrlName);
		}
	}
	
	public function updateAction() {
		$iId = $_POST['id'];
		
		$oEntity = Dao::entity($this->_sCtrlName, $iId, 'id_'.$this->_sCtrlName);
		
		$oEntity->setFields($_POST['dataset']);

		$aPossibleNameKeys = array('title', 'name');
		foreach ($aPossibleNameKeys as $key) {
			if (isset($_POST['dataset'][$key])) {
				$sName = $_POST['dataset'][$key];
				break;
			}
		}

		// slug by used name if empty or changed name
		if (isset($_POST['dataset']['slug']) && (empty($_POST['dataset']['slug']) || $_POST['dataset']['slug'] != $this->slugify($sName))) {
			$oEntity->setField('slug', $this->slugify($sName));
		}

		if (isset($_POST['dataset']['modification_date'])) {
			if ($_POST['dataset']['modification_date'] == '') {
				$oEntity->setField('modification_date', date('Y-m-d H:i:s'));
			}
		}
		
		if ($oEntity->update()) {
			$this->postUpdate($iId);

			$sEditUrl = BASE_URL.'/'.$this->_sCtrlName.'/'.$iId;
			if (isset($sName)) {
				$this->raiseInfo('Wpis <strong>'.$sName.'</strong> został zmieniony. <a href="'.$sEditUrl.'">Edytuj</a> ponownie.');
			} else {
				$this->raiseInfo('Wpis został zmieniony. <a href="'.$sEditUrl.'">Edytuj</a> ponownie.');
			}

			$this->addHistoryLog('update', $this->_sCtrlName, $iId);

			// $aStreamItem = $this->prepareStreamItem($iId, $_POST);
			// $this->addToStream($aStreamItem);
			
			$this->actionForward('index', $this->_sCtrlName, true);
		} else {
			$this->raiseError('Wystąpił nieoczekiwany wyjątek.');
			$this->actionForward('info', $this->_sCtrlName);
		}
	}

	public function deleteAction() {
		if (isset($_POST['ids'])) {
			$aIds = $_POST['ids'];
			// print_r($aIds);
		}

		// echo 'deleteAction';
		
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
				$this->raiseInfo('Wpis <strong>'.$aTitles[0].'</strong> został usunięty.');
			} elseif (count($aTitles) > 1) {
				$this->raiseInfo('Wpisy <strong>'.implode(', ', $aTitles).'</strong> zostały usunięte.');
			} else {
				$this->raiseError('Wystąpił nieoczekiwany wyjątek.');
			}
			$this->_oRenderer->assign('aMsgs', array($aMsg));
		}
	}

	public function removeAction() {
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
				
				if ($oEntity->delete()) {
				// if (true) {
					// echo 'DELETE ACTION...';
					$this->addHistoryLog('remove', $this->_sCtrlName, $id);
					// print_r($oInstance);
					$aTitles[] = $sTitle;
					// $this->actionForward('index', $this->_sCtrlName);
				}
			}

			// msg
			if (count($aTitles) == 1) {
				$this->raiseInfo('Wpis <strong>'.$aTitles[0].'</strong> został usunięty.');
			} elseif (count($aTitles) > 1) {
				$this->raiseInfo('Wpisy <strong>'.implode(', ', $aTitles).'</strong> zostały usunięte.');
			} else {
				$this->raiseError('Wystąpił nieoczekiwany wyjątek.');
			}
			$this->_oRenderer->assign('aMsgs', array($aMsg));
		}
	}

	// action hooks

	public function postInsert($iId) {}

	public function postUpdate($iId) {}

	// additional common tasks 

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

	public function prepareStreamItem($iId, $aPost) {
		$aStreamItem = array(
			'id' => $iId,
			'ctrl' => $this->_sCtrlName,
			'title' => $_POST['dataset']['title'],
			'slug' => $this->slugify($_POST['dataset']['title']),
			'category' => isset($_POST['hidden']['category']) ? $_POST['hidden']['category'] : '',
			'category_slug' => isset($_POST['hidden']['category']) ? $this->slugify($_POST['hidden']['category']) : '',
			'category_abbr' => isset($_POST['hidden']['abbr']) ? $this->slugify($_POST['hidden']['abbr']) : '',
			'creation_date' => date('Y-m-d H:i:s')
		);
		return $aStreamItem;
	}

	public function addToStream($aStreamItem) {
		$sFile = ROOT_DIR . '/../renaissance/app/cache/stream';
		if (file_exists($sFile)) {
			$aActivities = unserialize(file_get_contents($sFile));

			$aItems = array_reverse($aActivities);

			// if item exists in stream remove it, and place at the top
			$aReducedItems = array();
			foreach ($aItems as $ik => $item) {
				$sItemKey = (isset($item['ctrl']) ? $item['ctrl'] : 'news').'-'.(isset($item['id']) ? $item['id'] : $item['id_news']);
				if (!isset($aReducedItems[$sItemKey])) {
					unset($item);
				}
			}

			$aReducedItems[] = $aStreamItem;

			$aActivities = array_reverse($aReducedItems);;

			// print_r($aActivities);

			file_put_contents($sFile, serialize($aActivities));
		}
		
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

	public function raiseInfo($sMessage) {
		$aMsg = array();
		$aMsg['text'] = $sMessage;
		$aMsg['type'] = 'info';
		$this->_oRenderer->assign('aMsgs', array($aMsg));
	}

	public function raiseWarning($sMessage) {
		$aMsg = array();
		$aMsg['text'] = $sMessage;
		$aMsg['type'] = 'warning';
		$this->_oRenderer->assign('aMsgs', array($aMsg));
	}

	public function raiseError($sMessage) {
		$aMsg = array();
		$aMsg['text'] = $sMessage;
		$aMsg['type'] = 'alert';
		$this->_oRenderer->assign('aMsgs', array($aMsg));
	}
}