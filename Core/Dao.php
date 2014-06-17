<?php

class Dao {
	
	public static function entity($sName = null, $mIdentifier = 0, $sOwner = null) {
		if (file_exists(DAO_DIR.'entities/'.$sName.'Entity.php')) {
			require_once DAO_DIR.'entities/'.$sName.'Entity.php';
			$sEntity = $sName.'Entity';
		} else {
			require_once AYA_DIR.'/Dao/Entity.php';
			$sEntity = 'Entity';
			if ($sName) {
				if (strpos($sName, '-') !== false) {
					$sIdLabel = strtolower(str_replace('-', '_', $sName));
				} else {
					$sIdLabel = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $sName));
				}
			}
		}
		if ($sIdLabel) {
			return new $sEntity($mIdentifier, $sIdLabel);
		} else {
			return new $sEntity($mIdentifier);
		}
	}
	
	public static function collection($sName, $sOwner = null, $aParams = null) {
		if (file_exists(DAO_DIR.'/collections/'.$sName.'Collection.php')) {
		    //echo 'dao exists...';
			require_once DAO_DIR.'/collections/'.$sName.'Collection.php';
			$sCollection = $sName.'Collection';
		} else {
		    //echo 'dao not exists...';
			require_once AYA_DIR.'/Dao/Collection.php';
			$sCollection = 'Collection';
		}
		return new $sCollection($sName, $sOwner, $aParams);
	}

}