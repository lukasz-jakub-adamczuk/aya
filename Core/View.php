<?php
/**
 * abstrakcyjna klasa widoku
 * 
 * @author ash
 *
 */
abstract class View {
  
	/**
	 * nazwa widoku
	 * 
	 * @var string
	 */
	protected $_sViewName;
	
	/**
	 * nazwa akcji
	 * 
	 * @var string
	 */
	protected $_sActionName;
	
	/**
	 * nazwa dao (kolekcja, encja)
	 * 
	 * @var string
	 */
	protected $_sDaoName;
	
	/**
	 * indeks dao (tabela)
	 * 
	 * @var string
	 */
	protected $_sDaoIndex;
	
	/**
	 * obiekt renderujacy szablony
	 * 
	 * @var unknown_type
	 */
	protected $_oRenderer;
	
	
	public function __construct() {
		$this->_sViewName = str_replace('View', '', get_class($this));
		$aExplodedViewName = explode('_', preg_replace('/([a-z])([A-Z0-9])/', "$1_$2", $this->_sViewName));
		$this->_sActionName = array_pop($aExplodedViewName);
		$this->_sDaoName = implode('', $aExplodedViewName);
		$this->_sDaoIndex = strtolower(implode('_', $aExplodedViewName));
	}
	
	protected function setDaoName($sDaoName) {
		$this->_sDaoName = $sDaoName;
	}
	
	protected function setDaoIndex($sDaoIndex) {
		$this->_sDaoIndex = $sDaoIndex;
	}

	/**
	 * poczatkowa konfiguracja widoku
	 * 
	 * @param $oRenderer
	 * @return unknown_type
	 */
	public function init($oRenderer) {
		$this->_oRenderer = $oRenderer;
	}
	
	/**
	 * podstawowe dzialania widoku
	 * 
	 * @return unknown_type
	 */
	public function fill() {}

	/**
	 * startowe dzialania widoku
	 * 
	 * @return unknown_type
	 */
	protected function _runBeforeFill() {}
	
	/**
	 * dodatkowe dzialania widoku
	 * 
	 * @return unknown_type
	 */
	protected function _runAfterFill() {}
}

?>
