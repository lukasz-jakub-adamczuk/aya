<?php
/**
 * klasa ogolnego obiektu
 * przechowuje pojedynczy wiersz z bazy
 * 
 * @author ash
 *
 */
class Dao {
	
	public static function entity($sName, $mIdentifier = 0, $sOwner = null) {
		if (file_exists(DAO_DIR.'entities/'.$sName.'Entity.php')) {
			require_once DAO_DIR.'entities/'.$sName.'Entity.php';
			$sEntity = $sName.'Entity';
		} else {
			require_once AYA_DIR.DS.'Dao/Entity.php';
			$sEntity = 'Entity';
			$sIdLabel = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $sName));
		}
		return new $sEntity($mIdentifier, $sIdLabel);
	}
	
	public static function collection($sName, $sOwner = null) {
		if (file_exists(DAO_DIR.DS.'collections/'.$sName.'Collection.php')) {
		    //echo 'dao exists...';
			require_once DAO_DIR.DS.'collections/'.$sName.'Collection.php';
			$sCollection = $sName.'Collection';
		} else {
		    //echo 'dao not exists...';
			require_once AYA_DIR.DS.'Dao/Collection.php';
			$sCollection = 'Collection';
		}
		return new $sCollection($sName, $sOwner);
	}

}
?>
