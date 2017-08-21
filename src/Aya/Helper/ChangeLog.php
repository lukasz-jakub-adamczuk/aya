<?php

namespace Aya\Helper;

use Aya\Core\Dao;

class ChangeLog {

	public static function add($sActionType, $sTableName, $mId, $sLog = '') {
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
}