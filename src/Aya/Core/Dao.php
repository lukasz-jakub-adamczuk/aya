<?php

namespace Aya\Core;

class Dao {
    
    public static function entity($name = null, $identifier = 0) {
        $name = strtolower($name) === $name ? str_replace(' ', '', ucwords(str_replace('-', ' ', $name))) : $name;
        if (file_exists(DAO_DIR.'/Entity/'.$name.'Entity.php')) {
            require_once DAO_DIR.'/Entity/'.$name.'Entity.php';

            $entity = "Dao\\Entity\\$name";
            $sEntity = $entity.'Entity';
        } else {
            require_once AYA_DIR.'/src/Aya/Dao/Entity.php';
            $sEntity = "Aya\\Dao\\Entity";
        }
        if ($name) {
            if (strpos($name, '-') !== false) {
                $idLabel = strtolower(str_replace('-', '_', $name));
            } else {
                $idLabel = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
            }
        }
        if (isset($idLabel)) {
            return new $sEntity($identifier, $idLabel);
        } else {
            return new $sEntity($identifier);
        }
    }
    
    public static function collection($name, $sOwner = null, $aParams = null) {
        $name = strtolower($name) === $name ? str_replace(' ', '', ucwords(str_replace('-', ' ', $name))) : $name;
        $collectionFile = DAO_DIR.'/Collection/'.$name.'Collection.php';
        if (file_exists($collectionFile)) {
            // echo 'dao exists...';
            require_once $collectionFile;
            $collectionName = $name.'Collection';
            $sCollection = "Dao\\Collection\\$collectionName";
            // $sCollection = $collection.'Collection';
        } else {
            //echo 'dao not exists...';
            require_once AYA_DIR.'/src/Aya/Dao/Collection.php';
            $sCollection = "Aya\\Dao\\Collection";
        }
        // echo $sCollection;
        return new $sCollection($name, $sOwner, $aParams);
    }

}