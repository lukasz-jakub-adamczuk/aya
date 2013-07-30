<?php
abstract class Singleton {
	protected static $_instance;
	
	private function __construct() {}
	
	public static function instance() {}
}

class Db extends Singleton {

	/**
	 * konfiguracja polacznia z serwerem bazy
	 * 
	 * @var unknown_type
	 */
	private static $_aConfig;
	
	/**
	 * uchwyt do polacznia z serwerem bazy
	 * 
	 * @var unknown_type
	 */
	private $_handle;
	
	/**
	 * przetworzony wynik zapytania
	 * 
	 * @var unknown_type
	 */
	private $_result;
	
	/**
	 * uchwyt do wybranej bazy danych
	 * 
	 * @var unknown_type
	 */
	private $_database;
	
	/**
	 * zapytanie do bazy
	 * 
	 * @var unknown_type
	 */
	private $_query;
	
	/**
	 * pomocnyczy wynik zapytania
	 * 
	 * @var unknown_type
	 */
	private $_res;

	/**
	 * sposob w jaki serwer agreguje wyniki zapytan
	 * 
	 * @var unknown_type
	 */
	//private $_res_type = MYSQL_BOTH;
	private $_res_type = MYSQL_ASSOC;

	/**
	 * suma zapytan do bazy danych
	 * 
	 * @var unknown_type
	 */
	private $_iCounter;


	/**
	 * otwiera polaczenie z serwerem bazy danych
	 * ustaweia 
	 * 
	 * @param $aConfig
	 * @return unknown_type
	 */
	private function __construct($aConfig) {
		//$this->_aConfig = $aConfig;
		self::$_aConfig = $aConfig;
		$this->_handle = mysql_connect($aConfig['host'], $aConfig['user'], $aConfig['password'] ) or die( $this->error() );
		$this->_database = mysql_select_db($aConfig['database'], $this->_handle) or die( $this->error() );

		$this->_iCounter = 0;
	}

	/**
	 * zwraca obiekt Db
	 * 
	 * @param $aConfig
	 * @return unknown_type
	 */
	public static function getInstance($aConfig = null) {
		if(is_null(self::$_instance)) {
			self::$_instance = new Db($aConfig);
		}
		return self::$_instance;
	}

	/**
	 * wykonuje zapytanie do bazy danych
	 * 
	 * @param $query
	 * @param $error
	 * @return unknown_type
	 */
	public function execute($query, $error = 1) {
		if ($this->_res = mysql_query($query)) {
			++$this->_iCounter;
			return $this->_res;
		} else {
			if( $error == 1) {
				return $this->error();
			} else {
				return false;
			}
		}
	}

	/**
	 * przetwarza wynik zapytania do tablicy
	 * 
	 * @param $rs
	 * @return unknown_type
	 */
	public function fetchRow($rs = null) {
		if($rs === null) {
			$result = @mysql_fetch_array($this->_res, $this->_res_type);
			return $result;
		} else {
			$result = @mysql_fetch_array($rs, $this->_res_type);
			return $result;
		}
	}

	/**
	 * zwraca pojedynczy wiersz z tabeli w bazie
	 * 
	 * @param $rs
	 * @param $res_type
	 * @return unknown_type
	 */
	public function getRow($rs) {
		$this->_query = $this->execute($rs);
		$res = @mysql_fetch_array($this->_query, $this->_res_type);
		return $res;
	}

	/**
	 * zwraca pojedyncze pole z tabeli w bazie
	 * 
	 * @param $rs
	 * @return unknown_type
	 */
	public function getOne($rs) {
		$this->_query = $this->execute($rs);
		$res = @mysql_fetch_array($this->_query, $this->_res_type);
		return current($res);//['0'];
	}

	/**
	 * zwraca wiele wierszy z tabeli w bazie
	 * 
	 * @param $rs
	 * @param $key
	 * @return unknown_type
	 */
	public function getArray($rs, $key = null) {
		$this->execute($rs);
		
		$result = array();
		if ($key === null) {
			$cnt = 0;
			while ($row = $this->fetchRow() ) {
				$result[$cnt++] = $row;
			}
		} else {
			while ($row = $this->fetchRow() ) {
				$result[$row[$key]] = $row;
			}
		}
		return $result;
	}

	/**
	 * konczy dzialanie bledem
	 * 
	 * @return unknown_type
	 */
	public function error() { 
		switch(mysql_errno()) {
			case '2002':
				die('Blad polaczenia z baza danych MySQL');
			break;
			default:
				die(mysql_error());
			break;
		}
	}

	/**
	 * zwraca sume zapytan dla obiektu Db
	 * 
	 * @return unknown_type
	 */
	public function getQueryCounter() {
		return $this->_iCounter;
	}

	/**
	 * zamyka polaczenie z serwerem bazy danych
	 * 
	 * @return unknown_type
	 */
	public function disconnect() {
		mysql_close($this->_handle);
	}
}
?>
