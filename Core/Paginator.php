<?php
/**
 * klasa nawigacji
 * 
 * @author ash
 *
 */
class Paginator {
	
	const PAGINATOR_PAGES_LIMIT = 3;
	
	private $_sPagination;
	
	private $_sPreLink;
	
	private $_sPostLink;
	
	private $_sPrevLabel = 'prev';
	
	private $_sNextLabel = 'next';
	
	private $_aMode;
	
	private $_iPage = 1;
	
	private $_iSize;
	
	private $_iTotal;
	
	public function __construct($aNavigator) {
	    // total items
	    if (isset($aNavigator['total'])) {
		    $this->_iTotal = $aNavigator['total'];
		}
		// current page
		if (isset($aNavigator['page'])) {
		    $this->_iPage = $aNavigator['page'];
		}
		// items per side
		if (isset($aNavigator['size'])) {
            $this->_iPageSize = $aNavigator['size'];
		} else {
			$this->_iPageSize = DEFAULT_COLLECTION_PAGE_SIZE;
		}
	}
	
	public function configure($sMode, $sPreLink = null, $sPostLink = null) {
		$this->_aMode = explode('-', $sMode);
		if ($sPreLink !== null) {
			$this->_sPreLink = $sPreLink;
		}
		if ($sPostLink !== null) {
			$this->_sPostLink = $sPostLink;
		}
		
		return $this;
	}
	
	public function generate($sPrevLabel = null, $sNextLabel = null) {
		if ($sPrevLabel !== null) {
			$this->_sPrevLabel = $sPrevLabel;
		}
		if ($sNextLabel !== null) {
			$this->_sNextLabel = $sNextLabel;
		}
		
		
		$sPreLink = LOCAL_URL.'/'.$this->_sPreLink.'';
		$sPostLink = '';
		
		
		$sPreLink .= '/page/';
		
		
		$iStart = $this->_iPage + 1;

		if ($this->_iTotal > $this->_iPageSize) {
			$this->_sPagination = '<ul class="fr links pagination">';

			$iPages = ceil($this->_iTotal/$this->_iPageSize);
			
			// previous page
			if ($iStart > 1 && in_array('prev', $this->_aMode)) {
				$this->_sPagination .= '<li><a href="'.$sPreLink.($iStart-1).$sPostLink.'">'.$this->_sPrevLabel.'</a></li>';
			}
	
	        // archive
			if (in_array('archive', $this->_aMode)) {
				if ($iPages > 0) {
					for ($iPage = 1; $iPage <= $iPages; $iPage++) {
						if ($iPage < Paginator::PAGINATOR_PAGES_LIMIT || $iPage >= $iPages-Paginator::PAGINATOR_PAGES_LIMIT || ($iPage >= $iStart-1 && $iPage <= $iStart+1)) {
							if ($iPage == $iStart) {
								$this->_sPagination .= '<li><a href="'.$sPreLink.($iPage).$sPostLink.'" class="active">'.($iPage).'</a></li>';
							} else {
								$this->_sPagination .= '<li><a href="'.$sPreLink.($iPage).$sPostLink.'">'.($iPage).'</a></li>';
							}
						} else {
							$this->_sPagination .= '%';
						}
					}
				}
			}
			
			// single pages
			if (in_array('all', $this->_aMode)) {
				if ($iPages > 0) {
					for ($iPage = 1; $iPage <= $iPages; $iPage++) {
						if ($iPage == $iStart) {
							$this->_sPagination .= '<li><a href="'.$sPreLink.($iPage).$sPostLink.'" class="active">'.($iPage).'</a></li>';
						} else {
							$this->_sPagination .= '<li><a href="'.$sPreLink.($iPage).$sPostLink.'">'.($iPage).'</a></li>';
						}
					}
				}
			}

            // next page	
			if ($iPages > ($iStart) && in_array('next', $this->_aMode)) {
				$this->_sPagination .= '<li><a href="'.$sPreLink.($iStart+1).$sPostLink.'">'.$this->_sNextLabel.'</a></li>';
			}

						
			$this->_sPagination = preg_replace('/[%]+/', '<li><span class="separator">...</span></li>', $this->_sPagination);
			$this->_sPagination .= '</ul>';
			return $this->_sPagination;
		} else {
			return '';
		}
	}
}

