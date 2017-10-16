<?php

namespace Aya\Dao;

use Aya\Core\Db;
use Aya\Core\Debug;
use Aya\Core\Navigator;
use Aya\Dao\Paginator;

class Collection {

    protected $_sName;
    
    protected $_sOwner;
    
    protected $_sTable;

    protected $_bLoaded;

    protected $_mId;
    
    protected $_iSize = 25;

    protected $_aNavigator;

    protected $_db;
    
    protected $_aQueryFields = array('*');
    
    protected $_aConditions = array();
    
    protected $_aSearch = array();

    protected $_aRows = array();

    protected $_aWhere = array();


    protected $_sQuery;

    protected $_sSelect = '';
    
    protected $_sJoin = '';
    
    protected $_sWhere = '';
    
    protected $_sGroup = '';

    protected $_sOrder = '';

    protected $_sLimit = '';

    public function __construct($sName = null, $sNavigatorOwner = null, $aParams = null) {
        // ustawia nazwe, kluczowa do dalszych dzialan
        $this->_sName = $sName === null ? str_replace('Collection', '', get_class($this)) : $sName;
        // nazwa tabeli
        $this->_sTable = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', str_replace('-', '_', $this->_sName)));

        $this->_mId = isset($aParams['id']) ? $aParams['id'] : 'id_'.$this->_sTable;
        // $this->_mId = 'id_'.$this->_sTable;

        if ($aParams) {
            if (isset($aParams['search'])) {
                $this->_aSearch = $aParams['search'];
            }
        }

        //Debug::show($this->_aSearch);
        
        if ($sNavigatorOwner) {
            $this->_sOwner = $sNavigatorOwner;
        }
        //Debug::show($this->_sOwner, 'contruct owner');
        $this->_db = Db::getInstance();

        // $this->_init();
    }

    public function init() {
        // default navigator values (sorting)
        $this->_defaultNavigator();
        // //Debug::show($this->_aNavigator, '$this->_aNavigator');
        // load values from session storage
        $this->_loadNavigator();
        //Debug::show($this->_aNavigator, '$this->_aNavigator from Collection _prepare()');
    }

    protected function _prepare() {
        return $this->getSelectPart().' '.$this->getFromPart().' '.$this->getJoinPart().' '.$this->_getWhere().' '.$this->getGroupPart().' '.$this->getOrderPart().' '.$this->getLimitPart().'';
    }

    protected function _defaultNavigator() {
        // sorting and ordering
        if ($this->_sOwner === Navigator::getOwner()) {
            if (Navigator::is('sort')) {
                if (Navigator::is('order')) {
                    $this->orderby(Navigator::get('sort'), Navigator::get('order'));
                } else {
                    $this->orderby(Navigator::get('sort'));
                }
            }
        }
    }
    
    protected function _loadNavigator() {
        $this->_aNavigator = Navigator::load($this->_sOwner);
        
        if (isset($this->_aNavigator['size'])) {
            $this->_iSize = $this->_aNavigator['size'];
        }
        if (isset($this->_aNavigator['page'])) {
            $this->_iPage = $this->_aNavigator['page'];
        }
        if (isset($this->_aNavigator['search']) && $this->_aNavigator['search'] != '') {
            foreach ($this->_aSearch as $field) {
                $this->_aWhere[] = ''.$field.' LIKE "%'.$this->_aNavigator['search'].'%"';
            }
        }
        
        $aReserved = array('page', 'size', 'sort', 'order', 'search');


        
        foreach ($this->_aNavigator as $key => $val) {
            if (!in_array($key, $aReserved)) {
                if ($val !== 'null') {
                    $this->_aWhere[] = $this->_sTable.'.'.$key.'="'.$val.'"';
                }
            }
        }
    }

    private function _fillNavigator() {
        $this->_aNavigator['loaded'] = count($this->_aRows);

        // echo $this->_iSize;
        // print_r($this->_aNavigator);
        
        // total records
        if ($this->_iSize) {
            // echo '_' . $this->_iSize . '_iSize is true ';
            $this->_aNavigator['total'] = $this->getCount();
        }
    }

    public function load($iSize = null) {
        // using unicode charset
        // $this->_db->execute("SET NAMES utf8");

        //Debug::show($this->_sOwner, 'load() method');

        if ($iSize) {
            $this->_aNavigator['size'] = $this->_iSize = $iSize;
        }

        // tmp hack
        $iPage = 0;
        if (isset($this->_aNavigator['page'])) {
            // echo $this->_aNavigator['page'];
            $iPage = $this->_aNavigator['page'];
        }

        if ($this->_iSize !== -1) {
            $iPage = $iPage > 0 ? $iPage-1 : 0;
            $this->_sLimit = ' LIMIT '.($iPage) * $this->_iSize.','.$this->_iSize;
        }
        if ($this->_sSelect == '') {
            $this->_sSelect = 'SELECT *';
        }

        if (!$this->_sQuery) {
            $this->_sQuery = $this->_prepare();
        }

        // //Debug::show($this->_sQuery);

        // echo '_from Collection.php: '.$this->_sQuery.'_';
        // var_dump('_'.$this->_sQuery.'_');

        // sql cache
        $sqlPath = CACHE_DIR . '/sql';
        if (!file_exists($sqlPath)) {
            mkdir($sqlPath);
        }
        $sqlHash = md5($this->_sQuery);
        $sqlFile = $sqlPath.'/'.$sqlHash;
        if (file_exists($sqlFile)) {
            // echo 'from cache';
            $this->_aRows = unserialize(file_get_contents($sqlFile));
        } else {
            // using unicode charset
            $this->_db->execute("SET NAMES utf8");

            // echo 'from db';
            $this->_aRows = $this->_db->getArray($this->_sQuery, $this->_mId);

            file_put_contents($sqlFile, serialize($this->_aRows));
        }
        $this->_bLoaded = 1;
    }

    public function restore($aRows, $aNavigator) {
        $this->_aRows = $aRows;
        $this->_aNavigator = $aNavigator;
        $this->_bLoaded = 1;
    }

    public function getCount() {
        $this->_sQuery = 'SELECT COUNT('.$this->_mId.') AS total '.$this->getFromPart().''.$this->_getWhere().'';
        return $this->_db->getOne($this->_sQuery, 'total');
    }

    public function getOne($query) {
        // $this->_sQuery = 'SELECT COUNT('.$this->_mId.') AS total '.$this->getFromPart().''.$this->_getWhere().'';
        return $this->_db->getOne($query);
    }


    public function navSet($sName, $mValue) {
        Navigator::set($sName, $mValue);
    }
    
    public function navDefault($sName, $mValue) {
        if (Navigator::is($sName) === false) {
            Navigator::set($sName, $mValue);
        }
    }




    public function setPageSize($iSize) {
        $this->_iSize = $iSize;
    }

    public function setTable($table) {
        $this->_sTable = $table;
    }

    public function setPrimaryKey($primaryKey) {
        $this->_mId = $primaryKey;
    }

    public function setSearchFields($aSearch) {
        $this->_aSearch = $aSearch;
    }

    public function getNavigator() {
        return $this->_aNavigator;
    }
    
    public function getRow() {
        if ($this->_bLoaded == 0) {
            $this->load();
        }
        $this->_fillNavigator();

        return current($this->_aRows);
    }

    public function getRows() {
        if ($this->_bLoaded == 0) {
            $this->load();
        }
        $this->_fillNavigator();

        return $this->_aRows;
    }
    
    // public function getColumn($sColumn = 'name', $iPage = -1) {
    public function getColumn($sColumn = 'name') {
        if ($this->_bLoaded == 0) {
            $this->load();
        }
        $this->_fillNavigator();

        $aColumn = array();
        foreach ($this->_aRows as $rows => $row) {
            $aColumn[$rows] = $row[$sColumn];
        }
        return $aColumn;
    }



    /**
     * zwraca tresc zapytania sql
     * 
     * @return string
     */
    public function getQuery() {
        return $this->_sQuery;
    }
    
    // public function echoQuery() {
    //     echo '<p><strong>SQL:</strong> '.$this->_sQuery.'</p>';
    // }

    public function getSelectPart() {
        return $this->_sSelect;
    }

    public function getFromPart() {
        return 'FROM '.$this->_sTable.'';
    }

    public function getJoinPart() {
        return $this->_sJoin;
    }

    public function getGroupPart() {
        return $this->_sGroup;
    }

    public function getOrderPart() {
        return $this->_sOrder;
    }

    public function getLimitPart() {
        return $this->_sLimit;
    }


    public function setGroupPart($sOrder) {
        $this->_sGroup = $sOrder;
    }

    public function setOrderPart($sOrder) {
        $this->_sOrder = $sOrder;
    }
    

    public function query($sQuery) {
        $this->_sQuery = $sQuery;
    }

    public function select($mSelect) {
        if (is_array($mSelect)) {
            $this->_sSelect = 'SELECT ' . implode(',', $mSelect);
        } else {
            // echo $mSelect;
            $this->_sSelect = 'SELECT ' . $mSelect;
        }
    }

    
    /**
     * laczy tabele podczas zapytania
     * brak agrumentow laczy tabele po ich nazwach
     * z doklejonymi '_id'
     * 
     * @param $sJoinTableName nazwa drugiej tabeli
     * @param $sJoinTableKey nazwa klucza glownego drugiej tabeli
     * @param $sMainTableKey nazwa klucza glownego pierwszej tabeli
     * @return $this
     */
    public function leftJoin($sJoinTableName, $sJoinTableKey = '', $sMainTableKey = '') {
        if ($sJoinTableKey == '') {
            $sJoinTableKey = 'id_'.$sJoinTableName;
        }
        if ($sMainTableKey == '') {
            $sMainTableKey = $sJoinTableKey;
        }
        if (strpos($sJoinTableName, ' ') !== false) {
            $aTableNames = explode(' ', $sJoinTableName);
            $sJoinTableName = $aTableNames[0];
            $sJoinTableAlias = $aTableNames[1];
            $this->_sJoin .= ' LEFT JOIN '.$sJoinTableName.' AS '.$sJoinTableAlias.' ON('.$sJoinTableAlias.'.'.$sJoinTableKey.'='.$this->_sTable.'.'.$sMainTableKey.')';
        } else {
            $this->_sJoin .= ' LEFT JOIN '.$sJoinTableName.' ON('.$sJoinTableName.'.'.$sJoinTableKey.'='.$this->_sTable.'.'.$sMainTableKey.')';
        }
        return $this;
    }

    public function groupby($sGroup = 'name') {
        $this->_sGroup = ' GROUP BY '.$sGroup;
        return $this;
    }

    public function orderby($sOrder = 'name', $sDirection = 'ASC') {
        // default sorting by table fields
        // $sSort = '`'.$this->_sTable.'`.`'.$sOrder.'`';
        $sSort = str_replace('-', '_', $sOrder);
        
        // sorting by fields form joined table
        foreach ($this->_aQueryFields as $fk => $field) {
            if (strpos($field, ' AS ') !== false) {
                $aParts = explode(' AS ', $field);
                if (str_replace('-', '_', $sOrder) == trim($aParts[1])) {
                    // $sSort = trim($aParts[0]);
                }
            }
        }
        
        // sorting direction
        if ($sDirection == 'DESC' || $sDirection == 'desc') {
            $this->_sOrder = ' ORDER BY '.$sSort.' DESC';            
        } else {
            $this->_sOrder = ' ORDER BY '.$sSort.'';
        }
        
        return $this;
    }
    
    public function limit($iSize) {
        $this->_iSize = $iSize;
        return $this;
    }


    public function search($sField, $mValue) {
        $this->_sWhere .= ' WHERE '.$sField.' LIKE "%'.$mValue.'%"';
        return $this;
    }


    public function where($sField, $mValue) {
        // $this->_aWhere[] = '`'.$sField.'`="'.$mValue.'"';
        $this->_aWhere[] = ''.$sField.'='.(is_string($mValue) ? '"'.$mValue.'"' : $mValue).'';
    }

    
    private function _getWhere() {
        $sWhere = '';
        // print_r($this->_aWhere);
        if (!empty($this->_aWhere)) {
            $sWhere = ' WHERE ';
            $sWhere .= implode(' AND ', $this->_aWhere);
        }
        return $sWhere;
    }
}