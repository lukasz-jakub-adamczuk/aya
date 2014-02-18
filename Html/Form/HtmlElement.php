<?php

abstract class HtmlElement {

	protected $_sId;

	protected $_sName;

	protected $_sIdPrefix;
	
	protected $_sNamePrefix = 'dataset';

	protected $_sLabel;

	protected $_sType = 'text';
	
	protected $_sClass;
	
	protected $_sValue;
	
	protected $_sUnit;
	
	// protected $_sBefore;
	
	// protected $_sAfter;
	
	// protected $_dMultiplier;
	
	// protected $_iDivider;
	
	protected $_sFormat;
	
	protected $_sHelp;
	
	/**
	 * type danych do porownania
	 * 
	 * @var string
	 */
	protected $_sDataType = '';
	
	/**
	 * lista zasad walidacji elementu
	 * 
	 * @var array
	 */
	protected $_aRules = array();
	
	/**
	 * lista relacji z innymi czesciami obiektami formularza
	 * 
	 * @var array
	 */
	protected $_aRelations = array();
	
	/**
	 * zarzadzanie relacjami z innymi czesciami formularza
	 * 
	 * @var object
	 */
	public $_oRelations;
	
	/**
	 * lista bledow walidacji elementu
	 * 
	 * @var array
	 */
	protected $_aErrors = array();
	
	protected $_bRender = true;
	
	// protected $_bStandAloneValidation = true;
		
	protected $_bVisible = true;
	
	protected $_bEnabled = true;
	
	protected $_bEscape = true;
	
	/**
	 * info czy element jest interaktywny
	 * 
	 * @var boolean
	 */
	protected $_bInteractive = false;
	
	protected $_aTexts;
	
	protected $_sMode;
	
	protected $_sFormMode;
	
	public function __construct($sId, $aInput = null) {
		if ($aInput !== null) {
			if (isset($aInput['label'])) {
				$this->_sLabel = $aInput['label'];
			}
			if (isset($aInput['class'])) {
				$this->_sClass = $aInput['class'];
			}
			if (isset($aInput['value'])) {
				$this->_sValue = $aInput['value'];
			}
			if (isset($aInput['type'])) {
				$this->_sType = $aInput['type'];
			}

			if (isset($aInput['prefix'])) {
				$this->_sNamePrefix = $aInput['prefix'];
			}
			if (isset($aInput['unit'])) {
				$this->_sUnit = $aInput['unit'];
			}
			
			if (isset($aInput['visible'])) {
				$this->_bVisible = $aInput['visible'];
			}
			if (isset($aInput['enabled'])) {
				// TODO do poprawy jeszcze i rozpropadowania na reszte parametrow
				$mParam = $this->_parseParam($aInput['enabled'], $aInput['privileges'], $aInput['action']);
				if (!is_null($mParam)) {
					$aInput['enabled'] = $mParam;
					$this->_bEnabled = $aInput['enabled'];
				}
			}
			// aleternatywne sposoby odblokowania/zablokowania pola
			// TODO raczej sie powinno to zablokowac
			if (isset($aInput['disabled'])) {
				$this->_bEnabled = $aInput['disabled'];
			}
			if (isset($aInput['enable'])) {
				if ($aInput['enable'] == true) {
					$this->_bEnabled = true;
				}
				if ($aInput['enable'] == false) {
					$this->_bEnabled = false;
				}
			}
			if (isset($aInput['disable'])) {
				if ($aInput['disable'] == true) {
					$this->_bEnabled = false;
				}
				if ($aInput['disable'] == false) {
					$this->_bEnabled = true;
				}
			}
			if (isset($aInput['multiplier'])) {
				$this->_dMultiplier = $aInput['multiplier'];
			}
			if (isset($aInput['divider'])) {
				$this->_iDivider = $aInput['divider'];
			}
			if (isset($aInput['format'])) {
				$this->_sFormat = $aInput['format'];
			}
			
			if (isset($aInput['validation'])) {
				$this->_sValidation = $aInput['validation'];
			}
			if (isset($aInput['rules'])) {
				$this->_aRules = $aInput['rules'];
			}
			if (isset($aInput['join'])) {
				$this->_sJoin = $aInput['join'];
			}
			if (isset($aInput['render'])) {
				$this->_bRender = $aInput['render'];
			}
			if (isset($aInput['escape'])) {
				$this->_bEscape = $aInput['escape'];
			}
			
			// TODO przerobic to na fabryke relacji
			if (isset($aInput['relations'])) {
				$this->_aRelations = $aInput['relations'];
				$sRelationName = 'XhtmlRelation';
				if ($aInput['type'] == 'bool' || $aInput['type'] == 'checkbox') {
					$sRelationName = 'XhtmlCheckboxRelation';
				}
				if ($aInput['type'] == 'radio' || $aInput['type'] == 'switch') {
					$sRelationName = 'Xhtml'.str_replace(' ', '', ucwords(str_replace('-', ' ', $aInput['type']))).'Relation';
				}
				$this->_oRelations = new $sRelationName();
				//$this->_oRelations = new XhtmlRelation();
				$this->_oRelations->setRelations($aInput['relations']);
			}
			
			if (isset($aInput['mode'])) {
				$this->_sMode = $aInput['mode'];
			}
		
			// typ danych
			if (isset($aInput['datatype'])) {
				$this->_sDataType = $aInput['datatype'];
			}
			
			// TODO combine with interactions & complex control
			if (isset($aInput['before'])) {
				$this->_sBefore = $aInput['before'];
			}
			if (isset($aInput['after'])) {
				$this->_sAfter = $aInput['after'];
			}
			
			// ogolne teksty dla elementu danego typu
			if (isset($aInput['texts'])) {
				$this->_aTexts = $aInput['texts'];
			}
		}
		
		if ($sName === null) {
			$this->_sId = $sId.'-'.$this->_sType;
			$this->_sName = $sId.'['.$this->_sType.']';
		} else {
			$this->_sId = $sId;
			$this->_sName = str_replace('-', '_', $sName);
		}
		
		// after construct
		$this->postConstruct($aInput);
	}
	
	/**
	 * dodatkowa konfiguracja elementu
	 * 
	 * @param array $aInput parametry konfiguracyjne
	 */
	public function postConstruct($aInput) {}
	
	/**
	 * wykrywanie bledow konfiguracji formularza 
	 * 
	 * @param unknown_type $name
	 * @param unknown_type $arguments
	 */
	public function __call($name, $arguments) {
		// Note: value of $name is case sensitive.
//        echo sprintf("<!--XFormElement called unsupported method: %s, object-class: %s, object-id: %s, object-name: %s, arguments: %s -->", $name, get_class($this),$this->getId(), $this->getName(),print_r($arguments,true));
	}
	
	/**
	 * jakas metoda do parsowania parametrow
	 * 
	 * TODO bym z tego zrezygnowal
	 * 
	 * @param unknown_type $mParam
	 * @param unknown_type $sPrivilege
	 * @param unknown_type $sAction
	 */
	final private function _parseParam($mParam, $sPrivilege, $sAction) {
		if (is_array($mParam)) {
			if (isset($mParam[$sPrivilege])) {
				if (substr($mParam[$sPrivilege], 1, 6) == $sAction) {
					return substr($mParam[$sPrivilege], 8);
				} else {
					if (isset($mParam['default'])) {
//                        if (substr($mParam['default'], 1, 6) == $sAction) {
//                            return substr($mParam['default'], 8);
//                        }

						return $mParam['default'];
						// brak default
						return null;
					}
					// brak default
					return null;
				}
			}
			if (isset($mParam['default'])) {
//                if (substr($mParam['default'], 1, 6) == $sAction) {
//                    return substr($mParam['default'], 8);
//                }
				return $mParam['default'];
			}
			// brak default
			return null;
		}
		return $mParam;
	}
	
	/*
	 * SETTERS
	 */
	
	/**
	 * ustawia identyfikator elementu
	 * 
	 * @param string $sValue wartosc
	 * @return XhtmlElement
	 */
	public function setId($sValue) {
		$this->_sId = $sValue;
		return $this;
	}
	
	/**
	 * ustawia nazwe elementu
	 * 
	 * @param string $sValue wartosc
	 * @return XhtmlElement
	 */
	public function setName($sValue) {
		$this->_sName = $sValue;
		return $this;
	}
	
	/**
	 * ustawia etykiete elementu
	 * 
	 * @param string $sValue wartosc
	 * @return XhtmlElement
	 */
	public function setLabel($sValue) {
		$this->_sLabel = $sValue;
		return $this;
	}
	
	/**
	 * ustawia typ elementu
	 * 
	 * @param string $sValue 
	 * @return XhtmlElement
	 */
	public function setType($sValue) {
		$this->_sType = $sValue;
		return $this;
	}

	/**
	 * ustawia klase elementu
	 * 
	 * @param string $sValue wartosc
	 * @return XhtmlElement
	 */
	public function setClass($sValue) {
		$this->_sClass = $sValue;
		return $this;
	}

	/**
	 * ustawia tryb wyswietlania elementu
	 * 
	 * @param string $sMode tryb
	 * @return string
	 */
	public function setMode($sMode) {
		$this->_sMode = $sMode;
		return $this;
	}

	/**
	 * ustawia wartosc elementu
	 * 
	 * @param string $sValue wartosc
	 * @return XhtmlElement
	 */
	public function setValue($sValue) {
		$this->_sValue = $sValue;
		return $this;
	}
	
	/**
	 * ustawia jednostke elementu
	 * 
	 * @param string $sUnit
	 * @return XhtmlElement
	 */
	public function setUnit($sUnit) {
		$this->_sUnit = $sUnit;
		return $this;
	}
	
	/**
	 * ustawia zawartosc przed elementem
	 * 
	 * @param string $sBefore
	 * @return XhtmlElement
	 */
	public function setBefore($sBefore) {
		$this->_sBefore = $sBefore;
		return $this;
	}
	
	/**
	 * ustawia zawartosc po elemencie
	 * 
	 * @param string $sAfter
	 * @return XhtmlElement
	 */
	public function setAfter($sAfter) {
		$this->_sAfter = $sAfter;
		return $this;
	}
	
	/**
	 * ustawia etykiete dla interaktywnego elementu
	 * 
	 * @param string $sKey klucz
	 * @param string $sValue wartosc
	 * 
	 * @return XhtmlElement
	 */
	public function setInteractiveTriggerLabel($sKey, $sValue) {
		$this->_aInteractiveTriggerLabels[$sKey] = $sValue;
		return $this;
	}
	
	/**
	 * ustawia teksty dla elementu jesli jakies przeslano
	 * 
	 * @param array $aTexts lista tekstow elementu
	 * 
	 * @return void
	 */
	public function setTexts($aTexts) {
		if (isset($aTexts['label'])) {
			$this->_sLabel = $aTexts['label'];
		}
		if (isset($aTexts['help'])) {
			$this->_sHelp = $aTexts['help'];
		}
		if (isset($aTexts['hint'])) {
			$this->_sHint = $aTexts['hint'];
		}
		if (isset($aTexts['value'])) {
			$this->_sValue = $aTexts['value'];
		}
		if (isset($aTexts['unit'])) {
			$this->_sUnit = $aTexts['unit'];
		}
		if (isset($aTexts['before'])) {
			$this->_sBefore = $aTexts['before'];
		}
		if (isset($aTexts['after'])) {
			$this->_sAfter = $aTexts['after'];
		}
		
		if (isset($aTexts['triggers'])) {
			$this->_aInteractiveTriggerLabels = $aTexts['triggers'];
		}
	}
	
	/*
	 * GETTERS
	 */
	
	/**
	 * pobiera identyfikator elementu
	 * 
	 * FIXME moze nas kopnie brak domyslnego argumentu,
	 * bo formularz przy generowaniu pol i runAfter taki przekazuje
	 * 
	 * @return string|bool
	 */
	public function getId() {
		if (isset($this->_sId)) {
			return $this->_sId;
		} else {
			return false;
		}
	}

	/**
	 * pobiera nazwe elementu
	 * 
	 * @return string|bool
	 */
	public function getName() {
		if (isset($this->_sName)) {
			return $this->_sName;
		} else {
			return false;
		}
	}

	/**
	 * pobiera prefiks identyfikatora elementu
	 * 
	 * @return string|false
	 */
	public function getIdPrefix() {
		if (isset($this->_sIdPrefix)) {
			return $this->_sIdPrefix;
		} else {
			return false;
		}
	}

	/**
	 * pobiera prefiks nazwy elementu
	 * 
	 * @return string|false
	 */
	public function getNamePrefix() {
		if (isset($this->_sNamePrefix)) {
			return $this->_sNamePrefix;
		} else {
			return false;
		}
	}
	
	/**
	 * pobiera etykiete elementu
	 * 
	 * @return string|bool
	 */
	public function getLabel() {
		if (isset($this->_sLabel)) {
			return $this->_sLabel;
		} else {
			return false;
		}
	}
	
	/**
	 * pobiera typ elementu
	 * 
	 * @return string|false
	 */
	public function getType() {
		if (isset($this->_sType)) {
			return $this->_sType;
		} else {
			return false;
		}
	}
	
	/**
	 * pobiera tryb elementu
	 * 
	 * @return string|bool
	 */
	public function getMode() {
		if (isset($this->_sMode)) {
			return $this->_sMode;
		} else {
			return false;
		}
	}
	
	/**
	 * pobiera wartosc elementu
	 * 
	 * @return string
	 */
	public function getValue() {
		if (isset($this->_sValue)) {
			return $this->_sValue;
		} else {
			return false;
		}
	}
	
	/**
	 * pobiera wartosc elementu
	 * 
	 * @return string
	 */
	public function getAfter() {
		if (isset($this->_sAfter)) {
			return $this->_sAfter;
		} else {
			return false;
		}
	}
	
	/**
	 * pobiera relacje elementu
	 * 
	 * @return string
	 */
	public function getRelations() {
		if ($this->_aRelations) {
			return $this->_aRelations;
		} else {
			return false;
		}
	}
	
	/*
	 * ADDERS
	 */
	
	/**
	 * dodaje zasade walidacji elementu
	 * 
	 * @param string $sRule nazwa zasady
	 * 
	 * @return XhtmlElement
	 */
	public function addRule($sRule) {
		$this->_aRules[] = $sRule;
		return $this;
	}
	
	/**
	 * dodaje kolejna wartosc klasy
	 * 
	 * @param string $sClass klasa
	 */
	public function addClass($sClass) {
		if (strlen($this->_sClass) > 1) {
			$this->_sClass .= ' '.$sClass;
		} else {
			$this->_sClass = $sClass;
		}
		return $this;
	}
	
	/*
	 * CHECKERS
	 */
	
	/**
	 * czy element ma dana klase
	 * 
	 * @param string $sClass nazwa klasy
	 */
	public function hasClass($sClass) {
		if (strpos($this->_sClass, $sClass) !== false) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * czy renderowany jest element
	 * 
	 * @return boolean
	 */
	public function isRender() {
		return $this->_bRender;
	}
	
	/**
	 * czy element posiada relacje z innymi czesciami formularza
	 * 
	 * @return boolean
	 */
	public function hasRelations() {
		if (empty($this->_aRelations)) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * pobiera tresc bledu dla elementu
	 * 
	 * @return string
	 */
	protected function _showErrors() {
		if (empty($this->_aErrors)) {
			return '';
		} else {
			$s = '<ul class="msgs">';
			foreach ($this->_aErrors as $errors => $error) {
				$s .= '<li><span>'.$error.'</span></li>';
			}
			$s .= '</ul>';
			return $s;
		}
	}
	
	/**
	 * zwraca wartosci atrybutu class oraz reguly walidacji elementu
	 * 
	 * w zaleznosci od wartsci @var $_bStandAloneValidation
	 * 1) true - osobno generuje sie class i validation (niepoprawny atrybut)
	 * 2) false - generuje sie tylko class, ktory zawiera klasy i reguly oddzielone spacjami
	 * 
	 * @return string
	 */
	protected function _validationString() {
		$aRules = array();
		foreach ($this->_aRules as $rules => $rule) {
			if ($rule[0] == '(') {
				// (mode::insert)required
				// (mode::insert-many)required
				$sKey = substr($rule, 7, (strpos($rule, ')')-7)); // insert
				$sValue = substr($rule, strpos($rule, ')')+1); // required
				
				if ($sKey == $this->_sFormMode) {
					$aRules[] = $sValue;
				}
			} else {
				$aRules[] = $rule;
			}
		}
		if (false) {
			return ($this->_sClass
				? ' class="'.$this->_sClass.'"'
				: '');
		} else {
		return $this->_bStandAloneValidation
			? ($this->_sClass
				? ' class="'.$this->_sClass.'"'
				: '')
				.($aRules
				? ' validation="'.implode(' ', $aRules).'"'
				: '')
			: ($this->_sClass || $aRules
				? ' class="'.$this->_sClass
				.($this->_sClass && $this->_aRules
					? ' '
					: '')
				.implode(' ', $aRules).'"'
				: '');
		}       
	}
	
	/**
	 * TODO move to Interaction
	 * 
	 * wlacza interakcje elementu
	 * element ma link pokaz/ukryj haslo
	 * 
	 * @param boolean $bState stan interakcji elementu
	 * 
	 * @return XhtmlElement
	 */
	public function interactive($bState = true) {
		$this->_bInteractive = $bState;
		return $this;
	}

	/**
	 * pokazuje wiersz z elementem
	 * 
	 * @param boolean $bVisible widocznosc wiersza
	 * 
	 * @return XhtmlElement
	 */
	public function show($bVisible = true) {
		$this->_bVisible = $bVisible;
		return $this;
	}
	
	/**
	 * ukrywa wiersz z elementem
	 * 
	 * @return XhtmlElement
	 */
	public function hide() {
		$this->_bVisible = false;
		return $this;
	}
	
	/**
	 * generowanie wiersza z elementem w kodzie (true/false)
	 * 
	 * @param boolean $bRender generowanie wiersza
	 * 
	 * @return XhtmlElement
	 */
	public function render($bRender = true) {
		$this->_bRender = $bRender;
		return $this;
	}
	
	/**
	 * nie generowanie elementu w kodzie
	 * 
	 * @return XhtmlElement
	 */
	public function unrender() {
		$this->_bRender = false;
		return $this;
	}
	
	/**
	 * zmienia stan elementu na aktywny
	 * 
	 * @param boolean $bEnabled dostepnosc elementu
	 * 
	 * @return XhtmlElement
	 */
	public function enable($bEnabled = true) {
		$this->_bEnabled = $bEnabled;
		return $this;
	}
	
	/**
	 * zmienia stan elementu na nieaktywny
	 * 
	 * @return XhtmlElement
	 */
	public function disable() {
		$this->_bEnabled = false;
		return $this;
	}
	
	/**
	 * przetwarza identyfikator elementu
	 */
	protected function _elementId() {
		return $this->_sIdPrefix ? $this->_sIdPrefix.'-'.$this->_sId : $this->_sId;
	}
	
	/**
	 * przetwarza nazwe elementu
	 */
	protected function _elementName() {
		return $this->_sNamePrefix ? $this->_sNamePrefix.'['.$this->_sName.']' : $this->_sName;
	}

	/**
	 * przetwarza wartosc elementu
	 * 
	 * @param string $sValue
	 */
	protected function _elementValue($sValue = null) {
		$sValue = $sValue ? $sValue : $this->_sValue;

		$sValue = stripcslashes($sValue);
		
		if ($this->_bEscape) {
			//return $this->_sFormMode == 'xhtml' ? $this->_escape($sValue) : '{$aInfoForm.'.$this->getName().'}';
			return $this->_escape($sValue);
			
		} else {
			//return $this->_sFormMode == 'xhtml' ? $sValue : '{$aInfoForm.'.$this->getName().'}';
			return $sValue;
			
		}
	}
	
	/**
	 * obsluga ucieczki dla wartosci elementu
	 * 
	 * @param mixed $mValue wartosc elementu
	 */
	protected function _escape($mValue) {
		if (is_array($mValue)) {
			$aValues = array();
			foreach ($mValue as $values => $value) {
				$aValues[$values] = htmlspecialchars($value);
			}
			return $aValues;
		} else {
			return htmlspecialchars($mValue);
		}
	}

	/**
	 * porownuje dwie wartosci
	 * 
	 * @param mixed $mValue1 pierwsza wartosc
	 * @param mixed $mValue2 druga wartosc
	 */
	protected function _compareValues($mValue1, $mValue2) {
		if ($this->_sDataType) {
			switch($this->_sDataType) {
				case 'string':
					return (string)$mValue1 === (string)$mValue2;
				case 'bool':
					return (bool)$mValue1 === (bool)$mValue2;
				case 'int':
					return (int)$mValue1 === (int)$mValue2;
				case 'float':
					return (float)$mValue1 === (float)$mValue2;
				default:
					return $mValue1 == $mValue2;
			}
		} else {
			return $mValue1 == $mValue2;
		}
	}
	
	/**
	 * generuje element wedlug trybu
	 */
	protected function _renderAsMode() {
		if ($this->_sMode) {
			$sMethod = (is_string($this->_sMode) ? 'renderAs'.ucfirst($this->_sMode) : 'renderAs');
			if (method_exists($this, $sMethod)) {
				return $this->$sMethod();
			} else {
				return $this->renderElement();
			}
		} else {
			return $this->renderElement();
		}
	}
	
	/**
	 * generuje element jako span
	 * 
	 * @return string
	 */
	public function renderAsSpan() {
		if ($this->_sValue) {
			return $this->renderBefore().'<span id="'.$this->_elementId().'">'.$this->_elementValue().'</span>'.$this->renderUnit().$this->renderAfter();
		}
		return '';
	}
	
	/**
	 * generuje etykiete elementu
	 * 
	 * @return string
	 */
	public function renderLabel() {
		return '<label for="'.$this->_elementId().'">'.(isset($this->_sLabel) ? $this->_sLabel : $this->_sName).'</label>';
	}
	
	/**
	 * generuje element formularza
	 * 
	 * @return string
	 */
	public function renderElement() {
		return $this->renderBefore().'<span>Element '.$this->_sId.' has no rednerElement() method</span>'.$this->renderUnit().$this->renderAfter();
	}
	
	/**
	 * generuje jednostke elementu
	 * 
	 * @return string
	 */
	public function renderUnit() {
		return $this->_sUnit ? '<span class="unit">'.$this->_sUnit.'</span>' : '';
	}
	
	/**
	 * generuje zawartosc przed elementem
	 * 
	 * @return string|object
	 */
	public function renderBefore() {
		if ($this->_sBefore instanceof XhtmlElement) {
			return $this->_sBefore->_renderAsMode();
		} else {
			return $this->_sBefore ? '<span class="mr5">'.$this->_sBefore.'</span>' : '';
		}
	}
	
	/**
	 * generuje zawartość po elemencie
	 * 
	 * @return string|object
	 */
	public function renderAfter() {
		if ($this->_sAfter instanceof XhtmlElement) {
			return $this->_sAfter->_renderAsMode();
		} else {
			return $this->_sAfter ? '<span class="ml5">'.$this->_sAfter.'</span>' : '';
		}
	}
	
	/**
	 * generuje podpowiedz dla elementu
	 * 
	 * @return string
	 */
	public function renderInfo() {
		return $this->_sHelp ? '<p class="tooltip access-hide info"><span>'.$this->_sHelp.'</span></p>' : '';
	}

	/**
	 * renderuje wiersz z elementem formularza
	 * 
	 * @param array $aFormParams parametry konfiguracyjne
	 * 
	 * @return string
	 */
	public function create($aFormParams = null) {
		// FIXME moze nie trafia xhtml
		$this->_sFormMode = $aFormParams['modes']['output'];
		
		$this->_sIdPrefix = $aFormParams['form_id'];
		
		// TODO do poprawy
		$sElementMarginClass = ($this->_sType == 'bool' || ($this->_sType == 'checkbox' && $this->_sLabel == '')) ? 'element-margin-left ' : '';
		$bShowLabel = ($this->_sType == 'checkbox' && $this->_sLabel == '') ? false : true;
		
		return
			'<!-- '.$this->_sType.' -->'
			.'<div'.($this->_bVisible ? '' : ' style="display: none;"').'>'
				.($bShowLabel ? '<div'.($this->_bModernStyle ? '' : ' class="label-box"').'>'.$this->renderLabel().'</div>' : '')
				.'<div'.($this->_bModernStyle ? (empty($this->_aErrors) ? '' : ' class="e"') : ' class="'.$sElementMarginClass.'element-box'.($this->hasClass('full') ? ' full' : '').(empty($this->_aErrors) ? '' : ' e').'"').'>'
					.'<div class="element'.($this->hasClass('related') ? ' related' : '').'">'.$this->_renderAsMode().'</div>'
					.$this->renderInfo()
				.'</div>'
			.'</div>'."\n";
	}
}
