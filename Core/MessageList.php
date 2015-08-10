<?php

class MessageList {

	private static $_aMessages = array();

	public static function add($aMessage) {
		self::$_aMessages[] = $aMessage;
	}

	public static function get() {
		return self::$_aMessages;
	}

	public static function raiseInfo($sMessage) {
		$aMsg = array();
		$aMsg['text'] = $sMessage;
		$aMsg['type'] = 'info';
		self::add($aMsg);
	}

	public static function raiseWarning($sMessage) {
		$aMsg = array();
		$aMsg['text'] = $sMessage;
		$aMsg['type'] = 'warning';
		self::add($aMsg);
	}

	public static function raiseError($sMessage) {
		$aMsg = array();
		$aMsg['text'] = $sMessage;
		$aMsg['type'] = 'alert';
		self::add($aMsg);
	}
}