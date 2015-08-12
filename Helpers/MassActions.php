<?php

class MassActions {

	private static $_aActions = array(
		'delete' => array(
			'name' => 'Usuń',
			'icon' => 'trash',
			'title' => 'Usuń'
		),
		'remove' => array(
			'name' => 'USUŃ',
			'icon' => 'trash',
			'title' => 'Usuń',
			'color' => 'red'
		),
		'mark' => array(
			'name' => 'Oznacz',
			'icon' => 'checkmark',
			'title' => 'Oznacz jako przeczytane'
		),
	);

	static public function getStandardActions() {
		if (User::atLeast('moderator')) {
			return self::getActions(array('delete', 'remove'));
		}
		if (User::atLeast('editor')) {
			return self::getActions(array('delete'));
		}
	}

	static public function getActions($aConf) {
		$aActions = array();
		foreach ($aConf as $action => $ak) {
			$aActions[$ak] = self::$_aActions[$ak];
		}
		return $aActions;
	}
}