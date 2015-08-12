<?php

class Lock {

	static private $_aValues = array();

	static public function get($sName, $mId) {
		$sLockFile = TMP_DIR . '/locks/'.$sName.'-'.$mId.'.lock';
		if (self::exists($sName, $mId)) {
			return file_get_contents($sLockFile);
		} else {
			return false;
		}
	}

	static public function set($sName, $mId, $aUser) {
		$sLockFile = TMP_DIR . '/locks';
		if (!file_exists($sLockFile)) {
			mkdir($sLockFile);
		}
		$sLockFile .= '/'.$sName.'-'.$mId.'.lock';
		if (!self::exists($sName, $mId)) {
			file_put_contents($sLockFile, $aUser['id'].':'.$aUser['name']);
		}
	}

	static public function release($sName, $mId) {
		$sLockFile = TMP_DIR . '/locks/'.$sName.'-'.$mId.'.lock';
		unlink($sLockFile);
	}

	static public function exists($sName, $mId) {
		$sLockFile = TMP_DIR . '/locks/'.$sName.'-'.$mId.'.lock';
		if (file_exists($sLockFile)) {
			return true;
		} else {
			return false;
		}
	}
}