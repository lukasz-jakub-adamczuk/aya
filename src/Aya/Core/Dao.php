<?php

namespace Aya\Core;

class Dao {
    
    public static function entity($sName = null, $mIdentifier = 0) {
        $sName = strtolower($sName) === $sName ? str_replace(' ', '', ucwords(str_replace('-', ' ', $sName))) : $sName;
        if (file_exists(DAO_DIR.'/Entity/'.$sName.'Entity.php')) {
            require_once DAO_DIR.'/Entity/'.$sName.'Entity.php';

            $entity = "Dao\\Entity\\$sName";
            $sEntity = $entity.'Entity';
        } else {
            require_once AYA_DIR.'/Dao/Entity.php';
            $sEntity = 'Entity';
        }
        if ($sName) {
            if (strpos($sName, '-') !== false) {
                $sIdLabel = strtolower(str_replace('-', '_', $sName));
            } else {
                $sIdLabel = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $sName));
            }
        }
        if (isset($sIdLabel)) {
            return new $sEntity($mIdentifier, $sIdLabel);
        } else {
            return new $sEntity($mIdentifier);
        }
    }
    
    public static function collection($sName, $sOwner = null, $aParams = null) {
        $sName = strtolower($sName) === $sName ? str_replace(' ', '', ucwords(str_replace('-', ' ', $sName))) : $sName;
        $collectionFile = DAO_DIR.'/Collection/'.$sName.'Collection.php';
        if (file_exists($collectionFile)) {
            // echo 'dao exists...';
            require_once $collectionFile;
            $collectionName = $sName.'Collection';
            $sCollection = "Dao\\Collection\\$collectionName";
            // $sCollection = $collection.'Collection';
        } else {
            //echo 'dao not exists...';
            require_once AYA_DIR.'/Dao/Collection.php';
            $sCollection = 'Collection';
        }
        // echo $sCollection;
        return new $sCollection($sName, $sOwner, $aParams);
    }

}