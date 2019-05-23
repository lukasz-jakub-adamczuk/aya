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
			$action = key($_POST['action']).'Action';

			// if (isset($_POST['ids'])) {
				$this->$action();
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
        $id = 0;

        $post = $this->beforeInsert();

        $entity = Dao::entity($this->_ctrlName, $id);
        
        $entity->setFields($post['dataset']);

        // tournaments don't have creation_date
        if ($this->_ctrlName == 'cup') {
            $entity->unsetField('creation_date');
        }

        $possibleNameKeys = array('title', 'name');
        foreach ($possibleNameKeys as $key) {
            if (isset($post['dataset'][$key])) {
                $name = $post['dataset'][$key];
                break;
            }
        }
        // print_r($post['dataset']);

        // slug by used name if empty or changed name
        if (isset($name)
            && isset($post['dataset']['slug'])
            && (empty($post['dataset']['slug'])
            || $post['dataset']['slug'] != Text::slugify($name))) {
			$entity->setField('slug', Text::slugify($name));
		}

        // no creation date
        // TODO or creation date invalid
        if (empty($post['dataset']['creation_date']) && $this->_ctrlName != 'cup') {
            $entity->setField('creation_date', date('Y-m-d H:i:s'));
        }
        // if mod_date comes somehow
        if (empty($post['dataset']['modification_date'])) {
            $entity->unsetField('modification_date');
        }
        
        if ($id = $entity->insert(true)) {
            $this->afterInsert($id);
            // clear cache
            $sSqlCacheFile = CACHE_DIR . '/sql/collection/'.$this->_ctrlName.'-'.$this->_actionName.'';

            $this->raiseInfo('Wpis '.(isset($name) ? '<strong>'.$name.'</strong>' : '').' został utworzony.');

            ChangeLog::add('create', $this->_ctrlName, $id);

            // $this->addHistoryLog('create', $this->_ctrlName, $id);

            // $aStreamItem = $this->prepareStreamItem($id, $post);
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
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $mButton = isset($_POST['button']) ? $_POST['button']: null;
        
        $entity = Dao::entity($this->_ctrlName, $id, 'id_'.$this->_ctrlName);
        $entity->setFields($_POST['dataset']);
        
        // lock handling
        if (Lock::exists($this->_ctrlName, $id)) {
            $sLock = Lock::get($this->_ctrlName, $id);
            $aLockParts = explode(':', $sLock);
            if ($aLockParts[0] != $_SESSION['user']['id']) {
                $this->_renderer->assign('aLock', array('id' => $aLockParts[0], 'name' => $aLockParts[1]));
            }
        } else {
            Lock::set($this->_ctrlName, $id, $_SESSION['user']);
        }

        $possibleNameKeys = array('title', 'name');
        foreach ($possibleNameKeys as $key) {
            if (isset($_POST['dataset'][$key])) {
                $name = $_POST['dataset'][$key];
                break;
            }
        }

        // slug by used name if empty or changed name
		if (isset($_POST['dataset']['slug']) && (empty($_POST['dataset']['slug']) || $_POST['dataset']['slug'] != Text::slugify($name))) {
			$entity->setField('slug', Text::slugify($name));
		}

        if (isset($_POST['dataset']['modification_date'])) {
            if ($_POST['dataset']['modification_date'] == '') {
                $entity->setField('modification_date', date('Y-m-d H:i:s'));
            }
        }

        // depending on button pressed
		if ($mButton == 'publish') {
			$entity->setField('visible', 1);
		}
		// if ($mButton == 'unpublish') {
		// 	$entity->setField('visible', 0);
		// }
		if ($mButton == 'delete') {
			$entity->setField('deleted', 1);
		}
		if ($mButton == 'undelete') {
			$entity->setField('deleted', 0);
		}
        
        if ($entity->update()) {
            $editUrl = BASE_URL.'/'.$this->_ctrlName.'/'.$id;
            if (isset($name)) {
                $this->raiseInfo('Wpis '.(isset($name) ? '<strong>'.$name.'</strong>' : '').' został zmieniony. <a href="'.$editUrl.'">Edytuj</a> ponownie.');
            } else {
                $this->raiseInfo('Wpis został zmieniony. <a href="'.$editUrl.'">Edytuj</a> ponownie.');
            }

            // $this->addHistoryLog('update', $this->_ctrlName, $id);

            // $aStreamItem = $this->prepareStreamItem($id, $_POST);
            // $this->addToStream($aStreamItem);

            ChangeLog::add('update', $this->_ctrlName, $id);

            $this->afterUpdate($id);
            
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
                $entity = Dao::entity($this->_ctrlName, $id, 'id_'.$this->_ctrlName);

                $possibleNameKeys = array('title', 'name');
                foreach ($possibleNameKeys as $key) {
                    if ($entity->hasField($key)) {
                        $name = $entity->getField($key);
                    } else {
                        $name = $id;
                    }
                }

                $entity->setField('deleted', '1');

                $this->beforeDelete($name);
                
                if ($entity->update()) {
                    // $this->addHistoryLog('delete', $this->_ctrlName, $id);
                    ChangeLog::add('update', $this->_ctrlName, $id);
                    $aNames[] = $name;

                    $this->afterDelete($name);
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
                $entity = Dao::entity($this->_ctrlName, $id, 'id_'.$this->_ctrlName);

                $possibleNameKeys = array('title', 'name');
                foreach ($possibleNameKeys as $key) {
                    if ($entity->hasField($key)) {
                        $name = $entity->getField($key);
                    } else {
                        $name = $id;
                    }
                }
                $this->beforeRemove($name);
                
                if ($entity->delete()) {
                    // $this->addHistoryLog('remove', $this->_ctrlName, $id);
                    ChangeLog::add('delete', $this->_ctrlName, $id);
                    $aNames[] = $name;

                    $this->afterRemove($name);
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

    public function afterInsert($id) {}

    public function beforeUpdate() {}

    public function afterUpdate($id) {}

    public function beforeDelete($name) {}

	public function afterDelete($name) {}

    public function beforeRemove($name) {}

	public function afterRemove($name) {}

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
        $id = $_GET['id'];
        
        $entity = Dao::entity($this->_ctrlName, $id, 'id_'.$this->_ctrlName);
        
        $entity->setField($sField, $mValue);

        $name = $id;
        $possibleNameKeys = array('title', 'name');
        foreach ($possibleNameKeys as $key) {
            if (isset($_POST['dataset'][$key])) {
                $name = $_POST['dataset'][$key];
                break;
            }
        }

        // $this->beforeChange($id);
        // print_r($entity);
        // echo '.....................';
        // echo $entity->getQuery();
        
        if ($entity->update()) {
            // $this->afterUpdate($id);

            $editUrl = BASE_URL.'/'.$this->_ctrlName.'/'.$id;
            if (isset($name)) {
                $this->raiseInfo('Wpis '.(isset($name) ? '<strong>'.$name.'</strong>' : '').' został zmieniony. <a href="'.$editUrl.'">Edytuj</a> ponownie.');
            } else {
                $this->raiseInfo('Wpis został zmieniony. <a href="'.$editUrl.'">Edytuj</a> ponownie.');
            }

            ChangeLog::add('update', $this->_ctrlName, $id);

            // $this->afterChange($id);
            
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