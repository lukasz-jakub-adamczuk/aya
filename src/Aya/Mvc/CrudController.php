<?php

namespace Aya\Mvc;

use Aya\Core\Controller;
use Aya\Core\Dao;
use Aya\Helper\ChangeLog;
use Aya\Helper\Lock;
use Aya\Helper\MessageList;
use Aya\Helper\Text;

class CrudController extends Controller {
    
    public function indexAction() {
		// lock handling
		if (isset($_SESSION['user'])) {
			if (isset($_SESSION['user']['lock'])) {
				if (Lock::exists($_SESSION['user']['lock']['name'], (int)$_SESSION['user']['lock']['id'])) {
					Lock::release($_SESSION['user']['lock']['name'], (int)$_SESSION['user']['lock']['id']);
				}
			}
		}

		// decide what to do with action
		if (isset($_POST['action'])) {
			$sAction = key($_POST['action']).'Action';

			// if (isset($_POST['ids'])) {
				$this->$sAction();
			// }
        }

        if (!$this->getTemplateName()) {
            $this->setTemplateName('all-index');
        }
	}
    
    public function infoAction() {
        // lock handling
        if (isset($_SESSION['user'])) {
            if (Lock::exists($this->_ctrlName, (int)$_GET['id'])) {
                $sLock = Lock::get($this->_ctrlName, (int)$_GET['id']);
                $aLockParts = explode(':', $sLock);
                if ($aLockParts[0] != $_SESSION['user']['id']) {
                    $this->_renderer->assign('aLock', array('id' => $aLockParts[0], 'name' => $aLockParts[1]));
                }
            } else {
                Lock::set($this->_ctrlName, (int)$_GET['id'], $_SESSION['user']);
                $_SESSION['user']['lock'] = array('name' => $this->_ctrlName, 'id' => $_GET['id']);
                // Session::set('user.locks', array())
            }
        }
        // $_SESSION['user']['lock'] = array('name' => $this->_ctrlName, 'id' => $_GET['id']);
    }
    
    public function addAction() {
        if ($this->_renderer->templateExists($this->_ctrlName.'-info.tpl')) {
            $this->setTemplateName($this->_ctrlName.'-info');
        } else {
            $this->setTemplateName('all-info');
        }
        $this->setViewName(str_replace(' ', '', ucwords(str_replace('-', ' ', $this->_ctrlName.'-info'))));
    }
    
    public function insertAction() {
        $mId = 0;

        $aPost = $this->beforeInsert();

        $oEntity = Dao::entity($this->_ctrlName, $mId);
        
        $oEntity->setFields($aPost['dataset']);

        $aPossibleNameKeys = array('title', 'name');
        foreach ($aPossibleNameKeys as $key) {
            if (isset($aPost['dataset'][$key])) {
                $sName = $aPost['dataset'][$key];
                break;
            }
        }
        // print_r($aPost['dataset']);

        // slug by used name if empty or changed name
        if (isset($sName)
            && isset($aPost['dataset']['slug'])
            && (empty($aPost['dataset']['slug'])
            || $aPost['dataset']['slug'] != Text::slugify($sName))) {
			$oEntity->setField('slug', Text::slugify($sName));
		}

        // no creation date
        // TODO or creation date invalid
        if (empty($aPost['dataset']['creation_date'])) {
            $oEntity->setField('creation_date', date('Y-m-d H:i:s'));
        }
        // if mod_date comes somehow
        if (empty($aPost['dataset']['modification_date'])) {
            $oEntity->unsetField('modification_date');
        }
        
        if ($mId = $oEntity->insert(true)) {
            $this->afterInsert($mId);
            // clear cache
            $sSqlCacheFile = CACHE_DIR . '/sql/collection/'.$this->_ctrlName.'-'.$this->_actionName.'';

            $this->raiseInfo('Wpis '.(isset($sName) ? '<strong>'.$sName.'</strong>' : '').' został utworzony.');

            ChangeLog::add('create', $this->_ctrlName, $mId);

            // $this->addHistoryLog('create', $this->_ctrlName, $mId);

            // $aStreamItem = $this->prepareStreamItem($mId, $aPost);
            // $this->addToStream($aStreamItem);

            $this->redirect('index');
            // $this->actionForward('index', $this->_ctrlName, true);
        } else {
            // $this->raiseError('Wystąpił nieoczekiwany wyjątek.');
            // $this->actionForward('info', $this->_ctrlName);
            $this->redirect('info');
        }
    }
    
    public function updateAction() {
        $mId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $mButton = isset($_POST['button']) ? $_POST['button']: null;
        
        $oEntity = Dao::entity($this->_ctrlName, $mId, 'id_'.$this->_ctrlName);
        $oEntity->setFields($_POST['dataset']);
        
        // lock handling
        if (Lock::exists($this->_ctrlName, $mId)) {
            $sLock = Lock::get($this->_ctrlName, $mId);
            $aLockParts = explode(':', $sLock);
            if ($aLockParts[0] != $_SESSION['user']['id']) {
                $this->_renderer->assign('aLock', array('id' => $aLockParts[0], 'name' => $aLockParts[1]));
            }
        } else {
            Lock::set($this->_ctrlName, $mId, $_SESSION['user']);
        }

        $aPossibleNameKeys = array('title', 'name');
        foreach ($aPossibleNameKeys as $key) {
            if (isset($_POST['dataset'][$key])) {
                $sName = $_POST['dataset'][$key];
                break;
            }
        }

        // slug by used name if empty or changed name
		if (isset($_POST['dataset']['slug']) && (empty($_POST['dataset']['slug']) || $_POST['dataset']['slug'] != Text::slugify($sName))) {
			$oEntity->setField('slug', Text::slugify($sName));
		}

        if (isset($_POST['dataset']['modification_date'])) {
            if ($_POST['dataset']['modification_date'] == '') {
                $oEntity->setField('modification_date', date('Y-m-d H:i:s'));
            }
        }

        // depending on button pressed
		if ($mButton == 'publish') {
			$oEntity->setField('visible', 1);
		}
		// if ($mButton == 'unpublish') {
		// 	$oEntity->setField('visible', 0);
		// }
		if ($mButton == 'delete') {
			$oEntity->setField('deleted', 1);
		}
		if ($mButton == 'undelete') {
			$oEntity->setField('deleted', 0);
		}
        
        if ($oEntity->update()) {
            $sEditUrl = BASE_URL.'/'.$this->_ctrlName.'/'.$mId;
            if (isset($sName)) {
                $this->raiseInfo('Wpis '.(isset($sName) ? '<strong>'.$sName.'</strong>' : '').' został zmieniony. <a href="'.$sEditUrl.'">Edytuj</a> ponownie.');
            } else {
                $this->raiseInfo('Wpis został zmieniony. <a href="'.$sEditUrl.'">Edytuj</a> ponownie.');
            }

            // $this->addHistoryLog('update', $this->_ctrlName, $mId);

            // $aStreamItem = $this->prepareStreamItem($mId, $_POST);
            // $this->addToStream($aStreamItem);

            ChangeLog::add('update', $this->_ctrlName, $mId);

            $this->afterUpdate($mId);
            
            // $this->actionForward('index', $this->_ctrlName, true);
        } else {
            $this->raiseError('Wystąpił nieoczekiwany wyjątek.');
            // $this->actionForward('info', $this->_ctrlName);
        }
    }

    public function deleteAction() {
        if (isset($_GET['id'])) {
            $aIds = array($_GET['id']);
        }
        if (isset($_POST['ids'])) {
            $aIds = $_POST['ids'];
        }

        if (isset($aIds)) {
            $aNames = array();
            foreach ($aIds as $id) {
                $oEntity = Dao::entity($this->_ctrlName, $id, 'id_'.$this->_ctrlName);

                $aPossibleNameKeys = array('title', 'name');
                foreach ($aPossibleNameKeys as $key) {
                    if ($oEntity->hasField($key)) {
                        $sName = $oEntity->getField($key);
                    } else {
                        $sName = $id;
                    }
                }

                $oEntity->setField('deleted', '1');

                $this->beforeDelete($sName);
                
                if ($oEntity->update()) {
                    // $this->addHistoryLog('delete', $this->_ctrlName, $id);
                    ChangeLog::add('update', $this->_ctrlName, $id);
                    $aNames[] = $sName;

                    $this->afterDelete($sName);
                }
            }

            // msg
            if (count($aNames) == 1) {
                $this->raiseInfo('Wpis <strong>'.$aNames[0].'</strong> został usunięty.');
            } elseif (count($aNames) > 1) {
                $this->raiseInfo('Wpisy <strong>'.implode(', ', $aNames).'</strong> zostały usunięte.');
            } else {
                $this->raiseError('Wystąpił nieoczekiwany wyjątek.');
            }

			// prevent endless loop
			if (!isset($_POST['ids'])) {
				$this->actionForward('index', $this->_ctrlName, true);
			}
        }
    }

    public function removeAction() {
        if (isset($_GET['id'])) {
            $aIds = array($_GET['id']);
        }
        if (isset($_POST['ids'])) {
            $aIds = $_POST['ids'];
        }
        
        if (isset($aIds)) {
            $aNames = array();
            foreach ($aIds as $id) {
                $oEntity = Dao::entity($this->_ctrlName, $id, 'id_'.$this->_ctrlName);

                $aPossibleNameKeys = array('title', 'name');
                foreach ($aPossibleNameKeys as $key) {
                    if ($oEntity->hasField($key)) {
                        $sName = $oEntity->getField($key);
                    } else {
                        $sName = $id;
                    }
                }
                $this->beforeRemove($sName);
                
                if ($oEntity->delete()) {
                    // $this->addHistoryLog('remove', $this->_ctrlName, $id);
                    ChangeLog::add('delete', $this->_ctrlName, $id);
                    $aNames[] = $sName;

                    $this->afterRemove($sName);
                }
            }

            // msg
            if (count($aNames) == 1) {
                $this->raiseInfo('Wpis <strong>'.$aNames[0].'</strong> został usunięty.');
            } elseif (count($aNames) > 1) {
                $this->raiseInfo('Wpisy <strong>'.implode(', ', $aNames).'</strong> zostały usunięte.');
            } else {
                $this->raiseError('Wystąpił nieoczekiwany wyjątek.');
            }

			// prevent endless loop
			if (!isset($_POST['ids'])) {
				$this->actionForward('index', $this->_ctrlName, true);
			}
        }
    }

    // action hooks
    public function beforeInsert() {
        return $_POST;
    }

    public function afterInsert($mId) {}

    public function beforeUpdate() {}

    public function afterUpdate($mId) {}

    public function beforeDelete($sName) {}

	public function afterDelete($sName) {}

    public function beforeRemove($sName) {}

	public function afterRemove($sName) {}

    public function fetchTemplateAction() {
        $sPath = isset($_GET['path']) ? str_replace(',', '/', strip_tags($_GET['path'])) : null;

        if ($sPath) {
            $this->setContentType('template');

            $this->setTemplateName($sPath);
        }
    }

    // private methods
    protected function _changeStatusField($sField, $mValue) {
        // TODO validate
        $mId = $_GET['id'];
        
        $oEntity = Dao::entity($this->_ctrlName, $mId, 'id_'.$this->_ctrlName);
        
        $oEntity->setField($sField, $mValue);

        $sName = $mId;
        $aPossibleNameKeys = array('title', 'name');
        foreach ($aPossibleNameKeys as $key) {
            if (isset($_POST['dataset'][$key])) {
                $sName = $_POST['dataset'][$key];
                break;
            }
        }

        // $this->beforeChange($mId);
        // print_r($oEntity);
        // echo '.....................';
        // echo $oEntity->getQuery();
        
        if ($oEntity->update()) {
            // $this->afterUpdate($mId);

            $sEditUrl = BASE_URL.'/'.$this->_ctrlName.'/'.$mId;
            if (isset($sName)) {
                $this->raiseInfo('Wpis '.(isset($sName) ? '<strong>'.$sName.'</strong>' : '').' został zmieniony. <a href="'.$sEditUrl.'">Edytuj</a> ponownie.');
            } else {
                $this->raiseInfo('Wpis został zmieniony. <a href="'.$sEditUrl.'">Edytuj</a> ponownie.');
            }

            ChangeLog::add('update', $this->_ctrlName, $mId);

            // $this->afterChange($mId);
            
            $this->actionForward('index', $this->_ctrlName, true);
        } else {
            $this->raiseError('Wystąpił nieoczekiwany wyjątek.');
            $this->actionForward('info', $this->_ctrlName);
        }
    }

    public function raiseInfo($sMessage) {
        $aMsg = array();
        $aMsg['text'] = $sMessage;
        $aMsg['type'] = 'info';
        MessageList::add($aMsg);
    }

    public function raiseWarning($sMessage) {
        $aMsg = array();
        $aMsg['text'] = $sMessage;
        $aMsg['type'] = 'warning';
        MessageList::add($aMsg);
    }

    public function raiseError($sMessage) {
        $aMsg = array();
        $aMsg['text'] = $sMessage;
        $aMsg['type'] = 'alert';
        MessageList::add($aMsg);
    }
}