<?php

class HtmlText extends HtmlElement {
	
	protected $_sMaxLength;
	
	/**
	 * dodatkowa konfiguracja elementu
	 * 
	 * @param array $aInput parametry konfiguracyjne
	 */
	public function postConstruct($aInput) {
		if ($aInput !== null) {
			if (isset($aInput['class'])) {
				$this->_sClass = $aInput['class'];
			} else {
				$this->_sClass = 'w180';
			}
			if (isset($aInput['maxlength'])) {
				$this->_sMaxLength = $aInput['maxlength'];
			}
		}
	}
	
	/**
	 * ustawia maksymalna dlugosc wartosci elementu
	 * 
	 * @param string $sValue maksymalna dlugosc
	 * 
	 * @return XhtmlText
	 */
	public function setMaxLength($sValue) {
		$this->_sMaxLength = $sValue;
		return $this;
	}
	
	/**
	 * zwraca maksymalna dlugosc wartosci elementu
	 */
	public function getMaxLength() {
		return $this->_sMaxLength;
	}
	
	/**
	 * generuje element formularza
	 * 
	 * @return string
	 */
	public function renderElement() {
        return $this->renderBefore().'<input id="'.$this->_elementId().'" name="'.$this->_elementName().'" type="'.$this->_sType.'"'.(isset($this->_sValue) ? ' value="'.$this->_elementValue().'"' : '').$this->_validationString().($this->_sMaxLength ? ' maxlength="'.$this->_sMaxLength.'"' : '').($this->_bEnabled ? '' : ' disabled="disabled"').' />'.$this->renderUnit().$this->renderAfter();
	}	
}