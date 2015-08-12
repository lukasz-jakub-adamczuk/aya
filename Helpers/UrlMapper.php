<?php

class UrlMapper {

	private static $_aItems;

	private static $_aInvertedItems;

	static public function assignConfig($aItems) {
		self::$_aItems = $aItems;
		self::$_aInvertedItems = array_flip($aItems);
	}

	static public function get($sKey, $bInverted = false) {
		if ($bInverted) {
			return self::$_aInvertedItems[$sKey];
		} else {
			return self::$_aItems[$sKey];
		}
	}

	static public function getName($sKey, $bInverted = false) {
		if ($bInverted) {
			return self::$_aInvertedItems[$sKey]['name'];
		} else {
			return self::$_aItems[$sKey]['name'];
		}
	}

	static public function getUrl($sKey, $bInverted = false) {
		if ($bInverted) {
			return self::$_aInvertedItems[$sKey]['url'];
		} else {
			return self::$_aItems[$sKey]['url'];
		}
	}
}