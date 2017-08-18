<?php
// require_once AYA_DIR.'/Management/FrontController.php';

class CrudController extends FrontController {
    
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
            // unset($_POST['action']);

            // Debug::show('mass action...');
            // echo 'mass action';

            if (isset($_POST['ids'])) {
                $this->$sAction();
            }
        }
    }
    
    public function infoAction() {
        // lock handling
        if (isset($_SESSION['user'])) {
            if (Lock::exists($this->_sCtrlName, (int)$_GET['id'])) {
                $sLock = Lock::get($this->_sCtrlName, (int)$_GET['id']);
                $aLockParts = explode(':', $sLock);
                if ($aLockParts[0] != $_SESSION['user']['id']) {
                    $this->_renderer->assign('aLock', array('id' => $aLockParts[0], 'name' => $aLockParts[1]));
                }
            } else {
                Lock::set($this->_sCtrlName, (int)$_GET['id'], $_SESSION['user']);
                $_SESSION['user']['lock'] = array('name' => $this->_sCtrlName, 'id' => $_GET['id']);
                // Session::set('user.locks', array())
            }
        }
        // $_SESSION['user']['lock'] = array('name' => $this->_sCtrlName, 'id' => $_GET['id']);
    }
    
    public function addAction() {
        if ($this->_renderer->templateExists($this->_sCtrlName.'-info.tpl')) {
            $this->setTemplateName($this->_sCtrlName.'-info');
        } else {
            $this->setTemplateName('all-info');
        }
        $this->setViewName(str_replace(' ', '', ucwords(str_replace('-', ' ', $this->_sCtrlName.'-info'))));
    }
    
    public function insertAction() {
        $mId = 0;

        $aPost = $this->preInsert();

        $oEntity = Dao::entity($this->_sCtrlName, $mId);
        
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
        if (isset($sName) && isset($aPost['dataset']['slug']) && (empty($aPost['dataset']['slug']) || $aPost['dataset']['slug'] != $this->slugify($sName))) {
            $oEntity->setField('slug', $this->slugify($sName));
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
            $this->postInsert($mId);
            // clear cache
            $sSqlCacheFile = TMP_DIR . '/sql/collection/'.$this->_sCtrlName.'-'.$this->_sActionName.'';

            $this->raiseInfo('Wpis '.(isset($sName) ? '<strong>'.$sName.'</strong>' : '').' został utworzony.');

            $this->addHistoryLog('create', $this->_sCtrlName, $mId);

            // $aStreamItem = $this->prepareStreamItem($mId, $aPost);
            // $this->addToStream($aStreamItem);

            $this->actionForward('index', $this->_sCtrlName, true);
        } else {
            $this->raiseError('Wystąpił nieoczekiwany wyjątek.');
            $this->actionForward('info', $this->_sCtrlName);
        }
    }
    
    public function updateAction() {
        // lock handling
        if (Lock::exists($this->_sCtrlName, (int)$_GET['id'])) {
            $sLock = Lock::get($this->_sCtrlName, (int)$_GET['id']);
            $aLockParts = explode(':', $sLock);
            if ($aLockParts[0] != $_SESSION['user']['id']) {
                $this->_renderer->assign('aLock', array('id' => $aLockParts[0], 'name' => $aLockParts[1]));
            }
        } else {
            Lock::set($this->_sCtrlName, (int)$_GET['id'], $_SESSION['user']);
        }

        $mId = isset($_POST['id']) ? $_POST['id'] : 0;
        
        $oEntity = Dao::entity($this->_sCtrlName, $mId, 'id_'.$this->_sCtrlName);
        
        $oEntity->setFields($_POST['dataset']);

        $aPossibleNameKeys = array('title', 'name');
        foreach ($aPossibleNameKeys as $key) {
            if (isset($_POST['dataset'][$key])) {
                $sName = $_POST['dataset'][$key];
                break;
            }
        }

        // print_r($_POST['dataset']);

        // slug by used name if empty or changed name
        if (isset($_POST['dataset']['slug']) && (empty($_POST['dataset']['slug']) || $_POST['dataset']['slug'] != $this->slugify($sName))) {
            $oEntity->setField('slug', $this->slugify($sName));
        }

        if (isset($_POST['dataset']['modification_date'])) {
            if ($_POST['dataset']['modification_date'] == '') {
                $oEntity->setField('modification_date', date('Y-m-d H:i:s'));
            }
        }

        $sConvertSqlFile = TMP_DIR.'/files.sql';
        // $sConvertSql = TMP_DIR.'/texts';
        $sConvertSql = TMP_DIR.'/casperjs';
        // if (!file_exists($sConvertSql)) {
        //  mkdir($sConvertSql, 0777, true);
        // }

        // file_put_contents($sConvertSqlFile, $oEntity->getQuery().';'."\n\n");
        // echo $oEntity->getQuery();
        
        if ($oEntity->update()) {
            // $this->postUpdate($mId);

            // echo $oEntity->getQuery();

            // file_put_contents($sConvertSqlFile, $oEntity->getQuery().';'."\n\n", FILE_APPEND | LOCK_EX);
            // file_put_contents($sConvertSql.'/'.$this->_sCtrlName.'-'.$mId.'.sql', $oEntity->getQuery().';'."\n\n");

            $sEditUrl = BASE_URL.'/'.$this->_sCtrlName.'/'.$mId;
            if (isset($sName)) {
                $this->raiseInfo('Wpis '.(isset($sName) ? '<strong>'.$sName.'</strong>' : '').' został zmieniony. <a href="'.$sEditUrl.'">Edytuj</a> ponownie.');
            } else {
                $this->raiseInfo('Wpis został zmieniony. <a href="'.$sEditUrl.'">Edytuj</a> ponownie.');
            }

            $this->addHistoryLog('update', $this->_sCtrlName, $mId);

            // $aStreamItem = $this->prepareStreamItem($mId, $_POST);
            // $this->addToStream($aStreamItem);

            $this->postUpdate($mId);
            
            $this->actionForward('index', $this->_sCtrlName, true);
        } else {
            $this->raiseError('Wystąpił nieoczekiwany wyjątek.');
            $this->actionForward('info', $this->_sCtrlName);
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
                $oEntity = Dao::entity($this->_sCtrlName, $id, 'id_'.$this->_sCtrlName);

                $aPossibleNameKeys = array('title', 'name');
                foreach ($aPossibleNameKeys as $key) {
                    if ($oEntity->hasField($key)) {
                        $sName = $oEntity->getField($key);
                    } else {
                        $sName = $id;
                    }
                }

                $oEntity->setField('deleted', '1');
                
                if ($oEntity->update()) {
                    $this->addHistoryLog('delete', $this->_sCtrlName, $id);
                    $aNames[] = $sName;
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
            // $this->actionForward('index', $this->_sCtrlName, true);
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
                $oEntity = Dao::entity($this->_sCtrlName, $id, 'id_'.$this->_sCtrlName);

                $aPossibleNameKeys = array('title', 'name');
                foreach ($aPossibleNameKeys as $key) {
                    if ($oEntity->hasField($key)) {
                        $sName = $oEntity->getField($key);
                    } else {
                        $sName = $id;
                    }
                }
                
                if ($oEntity->delete()) {
                    $this->addHistoryLog('remove', $this->_sCtrlName, $id);
                    $aNames[] = $sName;
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
            // $this->actionForward('index', $this->_sCtrlName, true);
        }
    }

    // action hooks

    public function preInsert() {
        return $_POST;
    }

    public function postInsert($mId) {}

    public function preUpdate() {}

    public function postUpdate($mId) {}

    public function fetchTemplateAction() {
        $sPath = isset($_GET['path']) ? str_replace(',', '/', strip_tags($_GET['path'])) : null;

        if ($sPath) {
            $this->setContentType('template');

            $this->setTemplateName($sPath);
        }
    }

    // additional common tasks 

    public function addHistoryLog($sActionType, $sTableName, $mId, $sLog = '') {
        $oEntity = Dao::entity('change_log');

        $sUser = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 0;

        $oEntity->setField('id_author', $sUser);
        $oEntity->setField('id_record', $mId);
        $oEntity->setField('table', $sTableName);
        $oEntity->setField('log', $sLog);
        $oEntity->setField('creation_date', date('Y-m-d H:i:s'));
        $oEntity->setField('type', $sActionType);

        $oEntity->insert();
    }

    // private methods

    protected function _changeStatusField($sField, $mValue) {
        $mId = $_GET['id'];
        
        $oEntity = Dao::entity($this->_sCtrlName, $mId, 'id_'.$this->_sCtrlName);
        
        $oEntity->setField($sField, $mValue);

        $sName = $mId;
        $aPossibleNameKeys = array('title', 'name');
        foreach ($aPossibleNameKeys as $key) {
            if (isset($_POST['dataset'][$key])) {
                $sName = $_POST['dataset'][$key];
                break;
            }
        }
        
        if ($oEntity->update()) {
            // $this->postUpdate($mId);

            $sEditUrl = BASE_URL.'/'.$this->_sCtrlName.'/'.$mId;
            if (isset($sName)) {
                $this->raiseInfo('Wpis '.(isset($sName) ? '<strong>'.$sName.'</strong>' : '').' został zmieniony. <a href="'.$sEditUrl.'">Edytuj</a> ponownie.');
            } else {
                $this->raiseInfo('Wpis został zmieniony. <a href="'.$sEditUrl.'">Edytuj</a> ponownie.');
            }

            $this->addHistoryLog('change', $this->_sCtrlName, $mId);

            $this->postUpdate($mId);
            
            $this->actionForward('index', $this->_sCtrlName, true);
        } else {
            $this->raiseError('Wystąpił nieoczekiwany wyjątek.');
            $this->actionForward('info', $this->_sCtrlName);
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