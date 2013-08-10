<?php
require_once AYA_DIR.'/Core/Paginator.php';

/**
 * klasa ogolnej kolekcji
 * przechowuje wiele wierszy z bazy
 * 
 * @author ash
 *
 */
class Collection {

	/**
	 * nazwa (teoretycznie klasa)
	 * 
	 * @var string
	 */
	protected $_sName;
	
	protected $_sOwner;
	
	/**
	 * nazwa tabeli
	 * 
	 * @var unknown_type
	 */
	protected $_sTable;
	
	/**
	 * liczba wynikow na stronie
	 * 
	 * @var int
	 */
	protected $_iSize = 5;

	/**
	 * nawigacja dla zapytania
	 * pole sortujace, kierunek sortowania, itp
	 * 
	 * @var array
	 */
	protected $_aNavigator;

	/**
	 * obiekt polaczenia z baza danych
	 * 
	 * @var resource
	 */
	protected $_db;
	
	/**
	 * struktura tabeli xhtml
	 * 
	 * @var unknown_type
	 */
	protected $_aXhtmlTableFields = array();
	
	/**
	 * pola pobierane podczas zapytania
	 * domylnie wszystkie pola
	 * 
	 * @var array
	 */
	protected $_aQueryFields = array('*');
	
	protected $_aConditions = array();
	
	protected $_aSearch = array();

	/**
	 * przechowuje wynik zapytania
	 * 
	 * @var array
	 */
	protected $_aRows = array();
	
	/**
	 * tresc zapytania
	 * 
	 * @var string
	 */
	protected $_sQuery;
	
	/**
	 * czesc skladowa zapytania
	 * wymagana przy laczeniu tabel
	 * 
	 * @var string
	 */
	protected $_sJoin = '';
	
	/**
	 * czesc skladowa zapytania
	 * wymagana przy okreslaniu dokladniejszych
	 * kryteriow
	 * 
	 * @var string
	 */
	protected $_sWhere = '';
	
	protected $_aWhere = array();
	
	/**
	 * czesc skladowa zapytania
	 * wymagana przy grupowaniu
	 * 
	 * @var string
	 */
	protected $_sGroup = '';
	
	/**
	 * czesc skladowa zapytania
	 * wymagana przy sortowaniu
	 * 
	 * @var string
	 */
	protected $_sOrder = '';

	/**
	 * konstruktor
	 * 
	 */
	public function __construct($sName = null, $sNavigatorOwner = null, $aSearch = array('title')) {
	    // ustawia nazwe, kluczowa do dalszych dzialan
	    $this->_sName = $sName === null ? str_replace('Collection', '', get_class($this)) : $sName;
	    // nazwa tabeli
	    $this->_sTable = $this->_getName('underscore');
	    
	    $this->_aSearch = $aSearch;
	    
	    if ($sNavigatorOwner) {
	        $this->_sOwner = $sNavigatorOwner;
	    }
		$this->_db = Db::getInstance();
	}
	
	protected function _getName($sCase = null) {
	    if ($sCase == 'underscore') {
			return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $this->_sName));
		} elseif ($sCase == 'dash') {
			return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $this->_sName));
		} else {
		    return $this->_sName;
		}
	}

	/**
	 * pobiera krotka nazwe klasy
	 * domyslnie nazwa jest bez zmian
	 * inne formaty to 'lower', 'upper', 'caps' 
	 * 
	 * @param $sCase format nazwy klasy
	 * @return string
	 */
	protected function _getShortClassName($sCase = null) {
		if ($sCase == 'lower') {
			return strtolower(str_replace('_Collection', '', preg_replace('/([a-z])([A-Z])/', '$1_$2', get_class($this))));
		} elseif ($sCase == 'dashed') {
			return strtolower(str_replace('-Collection', '', preg_replace('/([a-z])([A-Z])/', '$1-$2', get_class($this))));
		} elseif ($sCase == 'caps') {
			return ucfirst(str_replace('Collection', '', get_class($this)));
		} else {
			return str_replace('Collection', '', get_class($this));
		}
	}
	
	/**
	 * zwraca obiekt wynik dane z zapytania kolekcji
	 * dane te wrzuca tez do cache
	 * 
	 * @param $iPage numer strony z wynikami
	 * @return unknown_type
	 */
	public function get($iPage = 1) {
		if ($iPage === -1) {
			$this->_iSize = -1;
		}
		
		$this->_getCollection();
	}
	
	/**
	 * pobiera kolekcje danych
	 * 
	 * @return unknown_type
	 */
	protected function _getCollection() {
		$this->fields(array('*'));
		$this->select();
	}

	/**
	 * ustawia liczbe wynikow na stronie
	 * 
	 * @param $iSize rozmiar strony
	 */
	public function setPageSize($iSize) {
		$this->_iSize = $iSize;
	}

	/**
	 * zwraca pola dla zapytania
	 * domyslnie wszyskie pola tabeli
	 * 
	 * @return string
	 */
	private function _getFields() {
		return implode(',', $this->_aQueryFields);
	}
	
	/**
	 * pobiera obiekt paginatora
	 * domyslny tryb to poprzedni-nastepny
	 * 
	 * @param $sMode tryb pracy paginatora
	 * @return string
	 */
	public function getPaginator($sMode = 'prev-next') {
		$oPaginator = new Paginator($this->_aNavigator);

		return $oPaginator->configure($sMode, './'.$this->_getName('dash').'')->generate();
	}
	
	/**
	 * przechowuje info nawigacyjne
	 * pole do sortowania, kierunek sortowania,
	 * numer strony, limit wynikow na stronie, itp
	 * 
	 * @return array
	 */
	public function getNavigator() {
		return $this->_aNavigator;
	}
	
	public function getRow() {
		return current($this->_aRows);
	}
	
	/**
	 * zwraca wiersze danych
	 * 
	 * @return array
	 */
	public function getRows() {
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
	
	public function echoQuery() {
        echo '<p><strong>SQL:</strong> '.$this->_sQuery.'</p>';
	}
	
	public function navSet($sName, $mValue) {
		Navigator::set($sName, $mValue);
	}
	
	public function navDefault($sName, $mValue) {
	    if (Navigator::is($sName) === false) {
	        Navigator::set($sName, $mValue);
	    }
	}

	/**
	 * ustawia pola dla zapytania
	 * 
	 * @param $aFields tablica z polami do zapytania
	 * @return array
	 */
	public function fields($aQueryFields) {
		$this->_aQueryFields = $aQueryFields;
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

	/**
	 * ustawia kryterium dla zapytania
	 * domyslnie operator '='
	 * 
	 * @param $sField nazwa pola
	 * @param $mValue wartosc pola
	 * @param $mOperator typ oeratora
	 * @return $this
	 */
	public function where($sField, $mValue, $mOperator = '=') {
		if ($this->_sWhere == '') {
			$this->_sWhere .= ' WHERE ';
		}
		$this->_sWhere .= ''.$sField.' '.$mOperator.' "'.$mValue.'"';
		
		return $this;
	}
	
	/**
	 * ustawia alernatywne (OR) kryterium dla zapytania
	 * domyslnie operator '='
	 * 
	 * @param $sField nazwa pola
	 * @param $mValue wartosc pola
	 * @param $mOperator typ oeratora
	 * @return $this
	 */
	public function orWhere($sField, $mValue, $mOperator = '=') {
		if ($this->_sWhere == '') {
			$this->_sWhere .= ' WHERE '.$sField.' '.$mOperator.' "'.$mValue.'" OR ';
		} else {
			$this->_sWhere .= ' OR '.$sField.' '.$mOperator.' "'.$mValue.'"';
		}
		return $this;
	}
	
	/**
	 * ustawia rownowazne (AND) kryterium dla zapytania
	 * domyslnie operator '='
	 * 
	 * @param $sField nazwa pola
	 * @param $mValue wartosc pola
	 * @param $mOperator typ oeratora
	 * @return $this
	 */
	public function andWhere($sField, $mValue, $mOperator = '=') {
		if ($this->_sWhere == '') {
			$this->_sWhere .= ' WHERE '.$sField.' '.$mOperator.' "'.$mValue.'" AND ';
		} else {
			$this->_sWhere .= ' AND '.$sField.' '.$mOperator.' "'.$mValue.'"';
		}
		
		return $this;
	}
	
	/**
	 * ustawia kryterium wyszukiwania dla zapytania
	 * 
	 * @param $sField nazwa pola
	 * @param $mValue wartosc pola
	 * @return $this
	 */
	public function search($sField, $mValue) {
		$this->_sWhere .= ' WHERE '.$sField.' LIKE "%'.$mValue.'%"';
	
		return $this;
	}

	/**
	 * grupuje wiersze podczas zapytania
	 * domyslne grupowanie po polu 'name'
	 * 
	 * @param $sGroup pole grupujace
	 * @return $this
	 */
	public function groupby($sGroup = 'name') {
		$this->_sGroup = ' GROUP BY '.$sGroup;
		return $this;
	}

	/**
	 * sortuje wiersze podczas zapytania
	 * domyslnie sortowanie po polu 'name'
	 * w kierunku rosnacym
	 * 
	 * @param $sOrder pole sortujace
	 * @param $sDirection kierunek sortowania
	 * @return $this
	 */
	public function orderby($sOrder = 'name', $sDirection = 'ASC') {
	    // default sorting by table fields
	    $sSort = '`'.$this->_sTable.'`.`'.$sOrder.'`';
	    
	    // sorting by fields form joined table
	    foreach ($this->_aQueryFields as $fk => $field) {
	        if (strpos($field, ' AS ') !== false) {
	            $aParts = explode(' AS ', $field);
	            if (str_replace('-', '_', $sOrder) == trim($aParts[1])) {
	                $sSort = trim($aParts[0]);
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
	
	/**
	 * ustawia limit wynikow na stronie
	 * potrzebny to ustawienia LIMIT w select()
	 * 
	 * @param $iSize liczba wynikow na stronie
	 * @return $this
	 */
	public function limit($iSize) {
		$this->_iSize = $iSize;
		return $this;
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
	
	/**
	 * wywoluje zapytanie sql
	 * domyslnie zerowa strona (pierwsza)
	 * 
	 * @param $start numer podstrony z wynikami
	 */
	public function select($iPage = 1) {
	    // default navigator values (sorting)
	    $this->_defaultNavigator();
	    // load values from session storage
	    $this->_loadNavigator();
	    
	    echo 'Owner in DB: '.$this->_sOwner;
	    
	    //print_r($this->_aNavigator);
	    
	    
		if ($this->_iSize === -1) {
			$sLimit = '';
		} else {
			$iPage = $iPage > 0 ? $iPage-1 : 0;
			$sLimit = ' LIMIT '.($iPage) * $this->_iSize.','.$this->_iSize;
    	}
		
		$this->_sQuery = 'SELECT '.$this->_getFields().' FROM '.$this->_sTable.''.$this->_sJoin.''.$this->_getWhere().''.$this->_sGroup.''.$this->_sOrder.''.$sLimit.'';
		$this->echoQuery();
		$this->_aRows = $this->_db->getArray($this->_sQuery, 'id_'.$this->_sTable);
		
		// total
		if ($this->_iSize === -1) {
		    $this->_aNavigator['total'] = count($this->_aRows);
		} else {
		    $this->_aNavigator['total'] = $this->getCount();
		}
	}
	
	public function getCount() {
	    $this->_sQuery = 'SELECT COUNT(id_'.$this->_sTable.') AS total FROM '.$this->_sTable.''.$this->_sJoin.''.$this->_getWhere().''.$this->_sGroup.''.$this->_sOrder.'';
	    //$this->echoQuery();
		return $this->_db->getOne($this->_sQuery, 'total');
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
?>
