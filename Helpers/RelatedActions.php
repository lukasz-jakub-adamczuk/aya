<?php

class RelatedActions {

    private static $_aActions = array(
        'refresh' => array(
            'name' => 'Usuń',
            'href' => '{$ctrl}',
            'icon' => 'refresh',
            'title' => 'Odśwież'
        ),
        'order' => array(
            'name' => 'Zmień kolejność',
            'href' => '{$ctrl}/order',
            'icon' => 'order',
            'title' => 'Zmień kolejność'
        ),
        'add' => array(
            'name' => 'Dodaj',
            'href' => '{$ctrl}/add',
            'icon' => 'add',
            'title' => 'Dodaj',
        )
    );

    static public function getStandardActions() {
        return self::getActions(array('add'));
    }

    static public function getActions($aConf) {
        $aActions = array();
        foreach ($aConf as $action => $ak) {
            $aActions[$ak] = self::$_aActions[$ak];
        }
        return $aActions;
    }
}