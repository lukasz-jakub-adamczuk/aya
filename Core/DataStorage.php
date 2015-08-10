<?php

class DataStorage {

	public static function is($sKey) {
		return file_exists($sKey);
	}

	public static function get($sKey) {
		if (self::is($sKey)) {
			return file_get_contents($sKey);
		}
	}

	public static function set($sKey, $mData) {
		file_put_contents($sKey, $mData);
	}

	public static function restore($sKey) {
		if (self::is($sKey)) {
			return unserialize(file_get_contents($sKey));
		}
	}

	public static function store($sKey, $mData) {
		file_put_contents($sKey, serialize($mData));
	}

	public static function checkStorage($sStorageDir) {
		if (!file_exists($sStorageDir)) {
			mkdir($sStorageDir, 0777, true);
		}
	}
}