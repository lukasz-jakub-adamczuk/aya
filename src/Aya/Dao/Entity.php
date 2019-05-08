<?php

namespace Aya\Dao;

use Aya\Core\Db;
use Aya\Core\Debug;

use Aya\Exception\MissingEntityException;

class Entity {
    
    protected $_id;
    
    protected $_idLabel;
    
    protected $_table;
    
    protected $_loaded;
    
    protected $_db;
    
    // values in db
    protected $_dbFields = [];
    
    // protected $_bModified;

    // values set manually
    protected $_queryFields;
    
    protected $_query;

    protected $_select;

    protected $_where;
    
    public function __construct($identifier = 0, $idLabel = null) {
        // $this->_table = strtolower(get_class($this)) == 'entity' ? $idLabel : null;
        $this->_table = $idLabel;
        $this->_db = Db::getInstance();

        // echo $this->_table;

        $this->_id = $identifier;
        if (is_numeric($identifier)) {
            $this->_idLabel = 'id_'.$this->_table;
            if ($identifier > 0) {
                $this->load();
            }
        }
        if ($idLabel) {
            $this->_idLabel = $idLabel;
        }
    }
    
    // protected function _getShortClassName($sMode = null) {
    //     if ($sMode == 'lowercase') {
    //         return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', str_replace('Entity', '', get_class($this))));
    //     } else {
    //         return ucwords(get_class($this));
    //     }
    // }

    public function query($query) {
        $this->_query = $query;
    }

    public function select($sSelect) {
        if (is_array($m))
        $this->_select = $sSelect;
    }

    public function where($where) {
        $this->_where = $where;
    }

    public function load() {
        $this->_select = '*';
        $this->_where = $this->_idLabel.'="'.$this->_id.'"';
        if (!$this->_query) {
            $this->_query = 'SELECT '.$this->_select.' FROM '.$this->_table.' WHERE '.$this->_where.'';
        }

        // Debug::show($this->_query);

        // sql cache
        $sqlPath = CACHE_DIR . '/sql';
        if (!file_exists($sqlPath)) {
            mkdir($sqlPath);
        }
        $sqlHash = md5($this->_query);
        $sqlFile = $sqlPath.'/'.$sqlHash;
        if (CACHE_SQL && file_exists($sqlFile)) {
            // echo 'from cache';
            $this->_dbFields = unserialize(file_get_contents($sqlFile));
        } else {
            // using unicode charset
            $this->_db->execute("SET NAMES utf8");

            // echo 'from db';
            $this->_dbFields = $this->_db->getRow($this->_query);

            file_put_contents($sqlFile, serialize($this->_dbFields));
        }

        // $this->_db->execute("SET NAMES utf8");

        // echo $this->_query;
        // echo 'from db';
        // $this->_dbFields = $this->_db->getRow($this->_query);
        // var_dump($this->_dbFields);
        
        // $this->_dbFields = $this->_db->getRow($this->_query);
        $this->_loaded = 1;

        // $this->_bModified = 0;
    }

    public function hasField($field) {
        if ($this->_loaded == 0) {
            $this->load();
        }
        if (isset($this->_dbFields[$field])) {
            return true;
        } else {
            return false;
        }
    }

    public function getField($field) {
        if ($this->_loaded == 0) {
            $this->load();
        }
        if (isset($this->_dbFields[$field])) {
            return $this->_dbFields[$field];
        } else {
            return false;
        }
    }

    public function getFields($returnObjectId = false) {
        if($this->_loaded == 0) {
            $this->load();
        }
        // print_r($this->_dbFields);
        if ($returnObjectId) {
            return array_merge($this->_dbFields, array('id' => $this->_id));
        }
        // if entity created with query identifier is 0;
        if (!$this->_id && isset($this->_dbFields['id_'.$this->_idLabel.''])) {
            $this->_id = $this->_dbFields['id_'.$this->_idLabel.''];
        }
        // what is really missing entity
        // if ($this->_dbFields === '') {
        if (empty($this->_dbFields)) {
            throw new MissingEntityException();
        }

        // Debug::show($this->_dbFields);

        return $this->_dbFields;
    }

    public function getId() {
        return $this->_id;
    }

    public function getQuery() {
        return $this->_query;
    }

    public function setField($field, $mValue) {
        // if (isset($this->_dbFields[$field])) {
        //     if (isset($mValue)) {
        //         if ($this->_dbFields[$field] != $mValue) {
        //             $this->_dbFields[$field] = $mValue;
        //             // $this->_aModifiedFields[$field] = 1;
        //             // $this->_bModified = 1;
        //         }
        //     }
        // } else {
        //     $this->_queryFields[$field] = $mValue;
        // }
        $this->_queryFields[$field] = $mValue;
    }

    public function setFields($aFields) {
        $this->_queryFields = $aFields;

        // foreach ($this->_dbFields as $key => $val) {
        //     if (isset($aFields[$key])) {
        //         if ($aFields[$key] != $val) {
        //             $this->_dbFields[$key] = $aFields[$key];
        //             // $this->_aModifiedFields[$key] = 1;
        //             // $this->_bModified = 1;
        //         }
        //     }
        // }
    }

    public function unsetField($field) {
        if (isset($this->_queryFields[$field])) {
            unset($this->_queryFields[$field]);
        }
    }

    public function increaseField($field, $increment = 1) {
        $sql = 'UPDATE '.$this->_table.' SET `'.$field.'` = `'.$field.'` + '.$increment.' WHERE id_'.$this->_idLabel.'="'.$this->_id.'"';
        $this->_query = $sql;
        
        if ($this->_db->execute($this->_query)) {
            return true;
        }
    }

    public function insert($setNamesUtf8 = false) {
        // print_r($this->_queryFields);
        $q = 'INSERT INTO '.$this->_table.'(';
        foreach ($this->_queryFields as $key => $val) {
            $q .= '`'.$key.'`, ';
        }
        $q = substr($q, 0, -2);
        $q .= ') VALUES (';
        foreach ($this->_queryFields as $key => $val) {
            // hack for inserting new record with null as PK
            if ($key == ('id_' . $this->_idLabel )) {
                $q .= 'NULL, ';
            } else {
                // echo $key.':'.$val;
                // echo $()
                if ($val === '__NULL__') {
                    // echo '0 is null;  ';
                    $q .= 'NULL, ';
                } else {
                    // var_dump($val);
                    $q .= '"'.is_string($val) ? '"'.addslashes($val).'", ' : ''.addslashes($val).', ' ;
                }
            }
        }
        $q = substr($q, 0, -2);
        $q .= ');';
        $this->_query = $q;
        // echo $q;
        // print_r($this->_queryFields);

        Debug::show($this->_query);

        if ($setNamesUtf8) {
            $this->_db->execute("SET NAMES utf8");
        }
        
        if ($statement = $this->_db->execute($q)) {
            // return $this->_id = mysql_insert_id();
            //$result = $statement->fetch(PDO::FETCH_ASSOC);
            
            return $this->_id = $this->_db->pdo()->lastInsertId();
        } else {
            return false;
        }
    }
    
    public function update($increments = false) {
        $q = 'UPDATE '.$this->_table.' SET ';
        // print_r($this->_queryFields);
        foreach ($this->_queryFields as $key => $val) {
            if ($val === '__NULL__') {
                $q .= '`'.$key.'`=NULL, ';
            } else {
                $q .= $increments ? '`'.$key.'`+'.$val.', ' : '`'.$key.'`="'.addslashes($val).'", ';
            }
        }
        $q = substr($q, 0, -2);
        if ($this->_where) {
            $q .= ' WHERE '.$this->_where;
        } else {
            $q .= ' WHERE id_'.$this->_idLabel.'="'.$this->_id.'"';
        }
        $this->_query = $q;
        // echo $this->_query;

        if ($this->_db->execute($this->_query)) {
            return true;
        }
        return false;
    }

    public function delete() {
        if ($this->_db->execute('DELETE FROM '.$this->_table.' WHERE '.$this->_where.'')) {
            return true;
        } else {
            return false;
        }
    }
}
