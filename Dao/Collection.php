<?php
require_once AYA_DIR.'/Core/Paginator.php';

class Collection {

	protected $_sName;
	
	protected $_sOwner;
	
	protected $_sTable;

	protected $_bLoaded;

	protected $_mId;
	
	protected $_iSize = 5;

	protected $_aNavigator;

	protected $_db;
	
	protected $_aQueryFields = array('*');
	
	protected $_aConditions = array();
	
	// protected $_aSearch = array();

	protected $_aRows = array();

	protected $_aWhere = array();


	protected $_sQuery;

	protected $_sSelect = '';
	
	protected $_sJoin = '';
	
	protected $_sWhere = '';
	
	protected $_sGroup = '';

	protected $_sOrder = '';

	public function __construct($sName = null, $sNavigatorOwner = null) {
		// ustawia nazwe, kluczowa do dalszych dzialan
		$this->_sName = $sName === null ? str_replace('Collection', '', get_class($this)) : $sName;
		// nazwa tabeli
		$this->_sTable = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', str_replace('-', '_', $this->_sName)));

		// echo '__'.$this->_sTable.'__';

		$this->_mId = 'id_'.$this->_sTable;
		
		// $this->_aSearch = $aSearch;
		$this->_aSearch = array('title');
		
		if ($sNavigatorOwner) {
			$this->_sOwner = $sNavigatorOwner;
		}
		$this->_db = Db::getInstance();
	}

	public function load($iSize = null) {
		// using unicode charset
		$this->_db->execute("SET NAMES utf8");

		if ($iSize) {
			$this->_iSize = $iSize;
		}

		$this->_sSelect = 'SELECT *';

		if (!$this->_sQuery) {
			$this->_sQuery = $this->_prepare();
		}

		Debug::show($this->_sQuery);
		

		$this->_aRows = $this->_db->getArray($this->_sQuery, $this->_mId);

		$this->_aNavigator['loaded'] = count($this->_aRows);
		
		// total
		if ($this->_iSize) {
		// 	$this->_aNavigator['total'] = count($this->_aRows);
		// } else {
			$this->_aNavigator['total'] = $this->getCount();
		}

		// $this->_select();
		$this->_bLoaded = 1;
	}

	protected function _prepare() {
		// default navigator values (sorting)
		$this->_defaultNavigator();
		Debug::show($this->_aNavigator, '$this->_aNavigator');
		// load values from session storage
		$this->_loadNavigator();
		Debug::show($this->_aNavigator, '$this->_aNavigator');

		// print $this->getOrderPart();


		// tmp hack
		$iPage = 0;
		if (isset($this->_aNavigator['page'])) {
			// echo $this->_aNavigator['page'];
			$iPage = $this->_aNavigator['page'];
		}
		// echo $this->_iSize;
		// $this->_iSize = -1;
		
		// $this->_sSelect = implode(',', $this->_aQueryFields);

		if ($this->_iSize === -1) {
			$sLimit = '';
		} else {
			$iPage = $iPage > 0 ? $iPage-1 : 0;
			$sLimit = ' LIMIT '.($iPage) * $this->_iSize.','.$this->_iSize;
		}
		return $this->getSelectPart().' '.$this->getFromPart().' '.$this->getJoinPart().''.$this->_getWhere().''.$this->getGroupPart().''.$this->getOrderPart().''.$sLimit.'';
	}

	public function getCount() {
		$this->_sQuery = 'SELECT COUNT('.$this->_mId.') AS total '.$this->getFromPart().''.$this->_getWhere().'';
		return $this->_db->getOne($this->_sQuery, 'total');
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
		Debug::show($this->_sOwner, 'Owner');
		
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

	public function getNavigator() {
		return $this->_aNavigator;
	}
	
	public function getRow() {
		if ($this->_bLoaded == 0) {
			$this->load();
		}
		return current($this->_aRows);
	}

	public function getRows() {
		if ($this->_bLoaded == 0) {
			$this->load();
		}
		return $this->_aRows;
	}
	
	public function getColumn($sColumn = 'name', $iPage = -1) {
		$this->get($iPage);
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
	// 	echo '<p><strong>SQL:</strong> '.$this->_sQuery.'</p>';
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
			$this->_sSelect = implode(',', $mSelect);
		} else {
			echo $mSelect;
			$this->_sSelect = $mSelect;
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



	
	private function _getWhere() {
		$sWhere = '';
		//print_r($this->_aWhere);
		if (!empty($this->_aWhere)) {
			$sWhere = ' WHERE ';
			$sWhere .= implode(' AND ', $this->_aWhere);
		}
		return $sWhere;
	}


	



}