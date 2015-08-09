<?php

class Breadcrumbs {

	static private $_aValues = array();

	static public function add($aItem) {
		if (isset($aItem['name'])) {
			self::$_aValues[$aItem['name']] = $aItem;
		} else {
			self::$_aValues[] = $aItem;
		}
	}

	static public function get() {
		return self::$_aValues;
	}
}