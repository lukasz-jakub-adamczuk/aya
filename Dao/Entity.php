<?php
/**
 * klasa ogolnego obiektu
 * przechowuje pojedynczy wiersz z bazy
 * 
 * @author ash
 *
 */
class Entity {
  
	/**
	 * identyfikator ogolnego obiektu
	 * 
	 * @var int
	 */
	protected $_iId;
	
	/**
	 * klucz glowny tabeli
	 * domyslnie sklejenie 'id_' i nazwy tabeli
	 * 
	 * @var string
	 */
	protected $_sIdLabel;
	
	/**
	 * nazwa tabeli
	 * 
	 * @var string
	 */
	protected $_sTable;
	
	/**
	 * stan obiektu (niewczytany/wczytany)
	 * 
	 * @var bool
	 */
	protected $_bLoaded;
	
	/**
	 * obiekt polaczenia z baza danych
	 * 
	 * @var resource
	 */
	protected $_db;
	
	/**
	 * pola tabeli ogolnego obiektu
	 * 
	 * @var array
	 */
	protected $_aTableFields = array();
	
	/**
	 * pola zapytania ogolnego obiektu
	 * 
	 * @var array
	 */
	protected $_aQueryFields = array();
	
	/**
	 * wartosci pol tabeli ogolnego obiektu
	 * 
	 * @var array
	 */
	protected $_aDbFields = array();
	
	/**
	 * zmodyfikowane pola ogolnego obiektu
	 * 
	 * @var array
	 */
	protected $_aModifiedFields = array();

	/**
	 * stan obiektu (niezmieniony/zmieniony)
	 * 
	 * @var bool
	 */
	protected $_bModified;
	
	/**
	 * zapytanie
	 * 
	 * @var string
	 */
	protected $_sQuery;
	
	/**
	 * rozmiar strony (ilosc wynikow na stronie)
	 * 
	 * @var int
	 */
	protected $_iPageSize = 1;
	
	/**
	 * tworzy obiekt ogolny
	 * 
	 * pobiera strukture tabeli
	 * 
	 * @param $mIdentifier wartosc identyfikatora obiektu
	 * @param $sIdLabel nazwa identyfikatora obiektu
	 * @return Generic Object
	 */
	public function __construct($mIdentifier = 0, $sIdLabel = null) {
		$this->_sTable = strtolower(get_class($this)) == 'entity' ? $sIdLabel : null;
		$this->_db = DB::getInstance();
		$this->_iId = $mIdentifier;
		if (is_numeric($mIdentifier)) {
			$this->_sIdLabel = 'id_'.$this->_sTable;
			if ($mIdentifier > 0) {
				$this->load();
			}
		} else {
//			$this->_is = $mIdentifier;
			$this->_sIdLabel = 'alias';
//			$this->_sIdLabel = $sIdLabel;
            //echo 'TESTTT_';
            //$this->load();
		}
		
		$this->_dbTableStructure();
	}

	/**
	 * ustawia strukture tabeli
	 * 
	 * @return unknown_type
	 */
	protected function _dbTableStructure() {}
	
	protected function _getShortClassName($sMode = null) {
		if ($sMode == 'lowercase') {
			return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', str_replace('Entity', '', get_class($this))));
		} else {
			return ucwords(get_class($this));
		}
	}

	/**
	 * pobiera obiekt paginatora
	 * TODO przeanalizowac
	 * 
	 * @param $sMode
	 * @return unknown_type
	 */
	public function getPaginator($sMode = 'prev-next') {
		/*$oPaginator = new Paginator($this->_sTable, $this->_getShortClassName(), $this->_iPageSize);
		
		if (isset($_GET['nav']['category'])) {
			$oPaginator->configure($sMode, $this->_getJoin(), $this->_sWhere, ','.$_GET['nav']['category'].',category');
		} else {
			$oPaginator->configure($sMode, $this->_getJoin(), $this->_sWhere, '');
		}

		return $oPaginator->generate();*/
	}
	
	/**
	 * wczytuje wartosci pol ogolnego obiektu z bazy
	 * ustawia nie
	 * 
	 * @return unknown_type
	 */
	public function load() {
		if ($this->_aQueryFields == null) {
			$sFields = '*';
		} else {
			$sFields = implode(', ', $this->_aQueryFields);
			//$fields = substr($fields, 0, strlen($fields)-2);
		}
		//echo 'SELECT '.$sFields.' FROM '.$this->_sTable.' WHERE '.$this->_sIdLabel.'="'.$this->_iId.'" ';
		$this->_aDbFields = $this->_db->getRow('SELECT '.$sFields.' FROM '.$this->_sTable.' WHERE '.$this->_sIdLabel.'="'.$this->_iId.'" ');
		$this->_bLoaded = 1;

		$this->_bModified = 0;
	}

	/**
	 * zwraca wartosc pola ogolnego obiektu
	 * najpierw wczytany zostaje stan obiektu z bazy
	 * 
	 * @param $sField
	 * @return mixed
	 */
	public function getField($sField) {
		if ($this->_bLoaded == 0) {
			$this->load();
		}
		if (isset($this->_aDbFields[$sField])) {
			//return stripslashes($this->_aDbFields[$sField]);
			return $this->_aDbFields[$sField];
		} else {
			return false;
		}
	}

	/**
	 * ustawia czesc pol do zapytania
	 * zwraca oczekiwane pola
	 * 
	 * @param $aFields 
	 * @return array
	 */
	public function getSomeFields($aQueryFields)  {
		$this->_aQueryFields = $aQueryFields;
		if($this->_bLoaded == 0) {
			$this->load();
		}
		return $this->_aDbFields;
	}

	/**
	 * zwraca wszystkie pola z tabeli
	 * 
	 * @return array
	 */
	public function getAllFields() {
		if($this->_bLoaded == 0) {
			$this->load();
			//echo 'load';
		}
		return $this->_aDbFields;
	}

	/**
	 * zwraca wartosc identyfikatora ogolnego obiektu
	 * 
	 * @return int
	 */
	public function getId() {
		return $this->_iId;
	}

	/**
	 * zwraca tresc zapytania do bazy
	 * 
	 * @return string
	 */
	public function getQuery() {
		return $this->_sQuery;
	}

	/**
	 * ustawia domyslna wartosc pola
	 * domyslna wartosc pola zostanie zmieniona
	 * przez @method setField() lub @method setFields()
	 * 
	 * @param $sField nazwa pola
	 * @param $mValue domyslna wartosc pola
	 * @return unknown_type
	 */
	public function setDefault($sField, $mDefaultValue) {
		if (isset($this->_aDbFields[$sField])) {
			if ($this->_aDbFields[$sField] != $mDefaultValue) {
				$this->_aDbFields[$sField] = $mDefaultValue;
				$this->_aModifiedFields[$sField] = 1;
				$this->_bModified = 1;
			}
		}
	}
	
	/**
	 * ustawia domyslne wartosci pol przed zapisem
	 * 
	 * @return unknown_type
	 */
	public function setDefaults() {}

	/**
	 * ustawia wartosc pola
	 * 
	 * @param $sField nazwa pola
	 * @param $mValue wartosc pola
	 * @return unknown_type
	 */
	public function setField($sField, $mValue) {
		if (isset($this->_aDbFields[$sField])) {
			if (isset($mValue)) {
				if ($this->_aDbFields[$sField] != $mValue) {
					$this->_aDbFields[$sField] = $mValue;
					$this->_aModifiedFields[$sField] = 1;
					$this->_bModified = 1;
				}
			}
		} else {
			$this->_aQueryFields[$sField] = $mValue;
		}
	}

	/**
	 * ustawia wszystkie pola ogolnego obiektu
	 * 
	 * @param $aFields wartosci pol
	 * @return unknown_type
	 */
	public function setFields($aFields) {
		$this->_aQueryFields = $aFields;

		foreach ($this->_aDbFields as $key => $val) {
			if (isset($aFields[$key])) {
				if ($aFields[$key] != $val) {
					$this->_aDbFields[$key] = $aFields[$key];
					$this->_aModifiedFields[$key] = 1;
					$this->_bModified = 1;
				}
			}
		}
	}
	
	public function hasField($sField) {
		if (in_array($sField, $this->_aTableFields['required']) || in_array($sField, $this->_aTableFields['optional'])) {
			return true;
		} else {
			return false;
		}
	}
	
	public function hasChanged($sField) {
		if (isset($this->_aModifiedFields[$sField]) && $this->_aModifiedFields[$sField] === 1) {
			return true;
		} else {
			return false;
		}
	}

	// TODO ustalic polityke aliasow
	public function createAlias($sValue, $sColumn = 'alias') {
		
		function clearString($sText) {
			return str_replace(
		    	array('ą', 'ć', 'ę', 'ń', 'ł', 'ó', 'ś', 'ź', 'ż', 'Ą', 'Ć', 'Ę', 'Ń', 'Ł', 'Ó', 'Ś', 'Ź', 'Ż'),
		    	array('a', 'c', 'e', 'n', 'l', 'o', 's', 'z', 'z', 'a', 'c', 'e', 'n', 'l', 'o', 's', 'z', 'z'),
		    	$sText);
		}
		$sAlias = strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), array('', '-', ''), clearString($sValue)));

		$intCount = $this->_db->getOne('SELECT COUNT('.$sColumn.') AS number FROM '.$this->_sTable.' WHERE '.$sColumn.' LIKE "'.$sAlias.'%"');
		if ($intCount > 0) {
			$aAliases = $this->_db->getArray('SELECT '.$sColumn.' FROM '.$this->_sTable.' WHERE '.$sColumn.' LIKE "'.$sAlias.'%"');
			$count = 0;
			
			foreach ($aAliases as $k => $v) {
				if (strpos($v['alias'], '-') !== false) {
					$sTmp = substr($v['alias'], (strrpos($v['alias'], '-'))+1);
					settype($sTmp, 'int');
					
					if (is_numeric($sTmp)) {
						if ($sTmp == 0) {
							$sTmp = 1;
						}
						$aNewAliases[$count++] = $sTmp;
					}
				}
			}
			
			if (empty($aNewAliases)) {
				$aNewAliases['0'] = 1;
			} 
			sort($aNewAliases);
			$aNewAliases = array_reverse($aNewAliases);
			$sAlias .= '-'.($aNewAliases['0']+1);
		}
		return $sAlias;
	}
	
	/**
	 * TODO ustalic polityke walidacji
	 * 
	 * @return unknown_type
	 */
	public function validate() {
		$bValid = true;
		$aMsgs = array();
		foreach ($this->_aTableFields['required'] as $k => $v) {
			if (empty($_POST[$this->_sTable][$v])) {
//				echo $_POST[$this->_sTable][$v];
				$bValid &= false;
				$aMsgs[$v] = array('class' => 'e', 'text' => 'puste pole');
			} else {
				$bValid &= true;
			}
		}

		if ((bool)$bValid === true) {
			return true;
		} else {
			return $aMsgs;
		}
	}

	/**
	 * dodaje nowy wiersz w tabeli
	 * 
	 * @return unknown_type
	 */
	public function insert() {
		$q = 'INSERT INTO '.$this->_sTable.'(';
		/*foreach ($this->_aTableFields['required'] as $key => $val) {
//			if (!is_numeric($key)) {
//				if ($val != '') {
					$q .= '`'.$val.'`, ';
//				}
//			}
		}
		foreach ($this->_aTableFields['optional'] as $key => $val) {
			if (isset($this->_aQueryFields[$val])) {
//				if ($val != '') {
					$q .= '`'.$val.'`, ';
//				}
			}
		}*/
		foreach ($this->_aQueryFields as $key => $val) {
		    $q .= '`'.$key.'`, ';
		}
		$q = substr($q, 0, -2);
		$q .= ') VALUES (';
		/*foreach ($this->_aTableFields['required'] as $key => $val) {
//		foreach ($this->_aQueryFields as $key => $val) {
//			if (!is_numeric($key)) {
//				if ($this->_aQueryFields['$val'] != '') {

					$q .= '"'.addslashes($this->_aQueryFields[$val]).'", ';
//				}
//			}
		}
		foreach ($this->_aTableFields['optional'] as $key => $val) {
			if (isset($this->_aQueryFields[$val])) {
//				if ($val != '') {
					$q .= '"'.addslashes($this->_aQueryFields[$val]).'", ';
//				}
			}
		}*/
		foreach ($this->_aQueryFields as $key => $val) {
		    $q .= '"'.addslashes($val).'", ';
		}
		$q = substr($q, 0, -2);
		$q .= ')';
		$this->_sQuery = $q;
		//echo $q;
		
		if ($this->_db->execute($q)) {
			return $this->_iId = mysql_insert_id();
//			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * aktualizuje wartosci danego wiersza w tabeli
	 * 
	 * @return unknown_type
	 */
	public function update() {
		if ($this->_bModified) {
			$q = 'UPDATE '.$this->_sTable.' SET ';
			foreach ($this->_aDbFields as $key => $val) {
				if (!is_numeric($key)) {
					if (isset($this->_aModifiedFields[$key])) {
//						if ($val === '') {
//							$q .= $key.'=NULL, ';
//						} else {
							$q .= '`'.$key.'`="'.addslashes($val).'", ';
//						}
					}
				}
			}
			$q = substr($q, 0, -2);
			$q .= ' WHERE '.$this->_sIdLabel.'="'.$this->_iId.'"';
			$this->_sQuery = $q;
			//echo $q;

			if ($this->_db->execute($q)) {
				return true; 
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * usuwa dany wiersz tabeli z bazy
	 * 
	 * @return unknown_type
	 */
	public function delete() {
		if ($this->_db->execute('DELETE FROM '.$this->_sTable.' WHERE '.$this->_sIdLabel.'='.$this->_iId.' ')) {
			return true;
		} else {
			return false;
		}
	}
}
?>
