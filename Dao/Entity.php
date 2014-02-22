<?php

class Entity {
	
	protected $_mId;
	
	protected $_sIdLabel;
	
	protected $_sTable;
	
	protected $_bLoaded;
	
	protected $_db;
	
	protected $_aDbFields = array();
	
	// protected $_bModified;

	protected $_aQueryFields;
	
	protected $_sQuery;

	protected $_sSelect;

	protected $_sWhere;
	
	public function __construct($mIdentifier = 0, $sIdLabel = null) {
		$this->_sTable = strtolower(get_class($this)) == 'entity' ? $sIdLabel : null;
		$this->_db = DB::getInstance();

		$this->_mId = $mIdentifier;
		if (is_numeric($mIdentifier)) {
			$this->_sIdLabel = 'id_'.$this->_sTable;
			if ($mIdentifier > 0) {
				$this->load();
			}
		}
		if ($sIdLabel) {
			$this->_sIdLabel = $sIdLabel;
		}
	}
	
	// protected function _getShortClassName($sMode = null) {
	// 	if ($sMode == 'lowercase') {
	// 		return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', str_replace('Entity', '', get_class($this))));
	// 	} else {
	// 		return ucwords(get_class($this));
	// 	}
	// }

	public function query($sQuery) {
		$this->_sQuery = $sQuery;
	}

	public function select($sSelect) {
		if (is_array($m))
		$this->_sSelect = $sSelect;
	}

	public function where($sWhere) {
		$this->_sWhere = $sWhere;
	}

	public function load() {
		$this->_sSelect = '*';
		$this->_sWhere = $this->_sIdLabel.'="'.$this->_mId.'"';
		if (!$this->_sQuery) {
			$this->_sQuery = 'SELECT '.$this->_sSelect.' FROM '.$this->_sTable.' WHERE '.$this->_sWhere.'';
		}

		Debug::show($this->_sQuery);

		$this->_db->execute("SET NAMES utf8");
		
		$this->_aDbFields = $this->_db->getRow($this->_sQuery);
		$this->_bLoaded = 1;

		// $this->_bModified = 0;
	}

	public function getField($sField) {
		if ($this->_bLoaded == 0) {
			$this->load();
		}
		if (isset($this->_aDbFields[$sField])) {
			return $this->_aDbFields[$sField];
		} else {
			return false;
		}
	}

	public function getFields() {
		if($this->_bLoaded == 0) {
			$this->load();
		}
		return $this->_aDbFields;
	}

	public function getId() {
		return $this->_mId;
	}

	public function getQuery() {
		return $this->_sQuery;
	}

	public function setField($sField, $mValue) {
		if (isset($this->_aDbFields[$sField])) {
			if (isset($mValue)) {
				if ($this->_aDbFields[$sField] != $mValue) {
					$this->_aDbFields[$sField] = $mValue;
					// $this->_aModifiedFields[$sField] = 1;
					// $this->_bModified = 1;
				}
			}
		} else {
			$this->_aQueryFields[$sField] = $mValue;
		}
		// $this->_aQueryFields[$sField] = $mValue;
	}

	public function setFields($aFields) {
		$this->_aQueryFields = $aFields;

		// foreach ($this->_aDbFields as $key => $val) {
		// 	if (isset($aFields[$key])) {
		// 		if ($aFields[$key] != $val) {
		// 			$this->_aDbFields[$key] = $aFields[$key];
		// 			// $this->_aModifiedFields[$key] = 1;
		// 			// $this->_bModified = 1;
		// 		}
		// 	}
		// }
	}

	public function insert() {
		$q = 'INSERT INTO '.$this->_sTable.'(';
		foreach ($this->_aQueryFields as $key => $val) {
			$q .= '`'.$key.'`, ';
		}
		$q = substr($q, 0, -2);
		$q .= ') VALUES (';
		foreach ($this->_aQueryFields as $key => $val) {
			if ($val == '_NULL_') {
				$q .= 'NULL, ';
			} else {
				$q .= '"'.addslashes($val).'", ';
			}
		}
		$q = substr($q, 0, -2);
		$q .= ');';
		$this->_sQuery = $q;

		Debug::show($this->_sQuery);
		
		if ($this->_db->execute($q)) {
			return $this->_mId = mysql_insert_id();
		} else {
			return false;
		}
	}
	
	public function update() {
		$q = 'UPDATE '.$this->_sTable.' SET ';
		foreach ($this->_aQueryFields as $key => $val) {
			if ($val == '_NULL_') {
				$q .= '`'.$key.'`=NULL, ';
			} else {
				$q .= '`'.$key.'`="'.addslashes($val).'", ';
			}
		}
		$q = substr($q, 0, -2);
		$q .= ' WHERE id_'.$this->_sIdLabel.'="'.$this->_mId.'"';
		$this->_sQuery = $q;
		// echo $this->_sQuery;

		if ($this->_db->execute($this->_sQuery)) {
			return true;
		} else {
			return false;
		}
	}

	public function delete() {
		if ($this->_db->execute('DELETE FROM '.$this->_sTable.' WHERE '.$this->_sWhere.'')) {
			return true;
		} else {
			return false;
		}
	}
}