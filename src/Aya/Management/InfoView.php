<?php

namespace Aya\Management;

use Aya\Core\Dao;
use Aya\Core\Debug;
use Aya\Core\Navigator;
use Aya\Core\View;

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

            $this->_renderer->assign('aFields', $aFields);

            // $this->postPoccessFields($aFields);

            Debug::show($oInstance->getFields());
            
            $sFormMode = 'update';
        } else {
            $sFormMode = 'insert';

            // $aFields = array();
        }

        $this->_renderer->assign('sFormMode', $sFormMode);

        // TODO better ctrl name
        $this->_renderer->assign('sFormMainPartTemplate', 'forms/'.$_GET['ctrl'].'-info-main.tpl');
        $this->_renderer->assign('sFormSubPartTemplate', 'forms/'.$_GET['ctrl'].'-info-sub.tpl');

        
        // insert or update with errors
        if (isset($_POST['dataset'])) {
            $this->_renderer->assign('aFields', $_POST['dataset']);
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