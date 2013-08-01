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
	
	private $_sTable;
	
	private $_sItem;
	
	private $_sQueryPart;
	
	private $_sPreLink;
	
	private $_sPostLink;
	
	private $_sPrevLabel = 'poprz.';
	
	private $_sNextLabel = 'nast.';
	
	private $_aMode;
	
	private $_iPageSize;
	
	private $_iTotal;
	
	protected $_aNavDetails;
	
	private $_aIgnoredParams = array();
	
	public function __construct($sTable, $sItem, $iPageSize, $sPreLink = '', $sPostLink = '') {
		$this->_sTable = $sTable;
		$this->_sItem = 'id_'.strtolower($sItem);
		$this->_sPreLink = $sPreLink;
		if ($iPageSize == 0) {
			$this->_iPageSize = DEFAULT_COLLECTION_PAGE_SIZE;
		} else {
			$this->_iPageSize = $iPageSize;
		}		
	}

	public function ignoreParam($mParam) {
		if (is_array($mParam)) {
			$this->_aIgnoredParams = $mParam;
		} else {
			$this->_aIgnoredParams[$mParam] = true;
		}
		return $this;
	}
	
	public function configure($sMode, $sQueryPart, $iTotal = null, $sPreLink = null, $sPostLink = null) {
		$this->_aMode = explode('-', $sMode);
		$this->_sQueryPart = $sQueryPart;
		if ($sPreLink !== null) {
			$this->_sPreLink = $sPreLink;
		}
		if ($sPostLink !== null) {
			$this->_sPostLink = $sPostLink;
		}
		if ($iTotal !== null) {
			$this->_iTotal = $iTotal;
		}
		
//		$this->_sPreLink = $sPreLink;
		if (SQL_CACHE_ENABLED || !isset($this->_iTotal)) {
		    echo 'TOTAL is known';
			if (Cache::exist($this->_sTable.'Total')) {
				$this->_iTotal = Cache::restore($this->_sTable.'Total');
			} else {
				$this->_iTotal = DB::getInstance()->getOne('SELECT COUNT('.$this->_sItem.') FROM '.$this->_sTable.' '.$this->_sJoin.' '.$this->_sWhere);
				Cache::save($this->_sTable.'Total', $this->_iTotal);
			}
		} else {
//			if (in_array('details', $this->_aMode)) {
//				$this->_aNavDetails = DB::getInstance()->getArray('SELECT entry.id_entry, entry.alias, entry.title FROM '.$this->_sTable.' LEFT JOIN category ON(entry.id_category=category.id_category) ORDER BY entry.created', 'alias');
//			} elseif (in_array('arch', $this->_aMode)) {
//				$this->_iTotal = DB::getInstance()->getOne('SELECT COUNT(entry.'.$this->_sItem.') FROM '.$this->_sTable.' '.$this->_sWhere);
//			} else {
				//$this->_iTotal = DB::getInstance()->getOne('SELECT COUNT(entry.'.$this->_sItem.') FROM '.$this->_sTable.' '.$this->_sQueryPart);
//				echo '<p>SELECT COUNT('.$this->_sTable.'.'.$this->_sItem.') FROM '.$this->_sTable.' '.$this->_sQueryPart.'</p>';
				$this->_iTotal = DB::getInstance()->getOne('SELECT COUNT('.$this->_sTable.'.'.$this->_sItem.') FROM '.$this->_sTable.' '.$this->_sQueryPart);
//				echo 'SELECT COUNT('.$this->_sTable.'.'.$this->_sItem.') FROM '.$this->_sTable.' '.$this->_sQueryPart;
				
//			}
		}
//		echo $this->_iTotal;
		return $this;
//		return $this->generate();
	}
	
	public function generate($sPrevLabel = null, $sNextLabel = null) {//$item, $pre_link, $post_link, $start, $where=null) {
		if ($sPrevLabel !== null) {
			$this->_sPrevLabel = $sPrevLabel;
		}
		if ($sNextLabel !== null) {
			$this->_sNextLabel = $sNextLabel;
		}
//		$prev = 'Starsze wpisy';
//		$next = 'Nowsze wpisy';
//		$prev = 'Nowsze wpisy';
//		$next = 'Starsze wpisy';
		//$pre_link = LOCALHOST_PATH.$_GET['ctrl'].'/index/';
		$sPreLink = LOCAL_URL.'/'.$this->_sPreLink.'';
		$sPostLink = '';
		
		
//		Router::debug($this);

		// ewentualne ignorowanie parametrow listingu z poziomu kolekcji
		if (!isset($this->_aIgnoredParams['all']) && !isset($this->_aIgnoredParams['search'])) {
		} else {
			if (isset($_REQUEST['nav']['search']) && $_REQUEST['nav']['search'] !== '') {
				$sPreLink .= '/search/'.$_REQUEST['nav']['search'];
			}
		}
		// ewentualne ignorowanie parametrow listingu z poziomu kolekcji 
		if (!isset($this->_aIgnoredParams['all']) && !isset($this->_aIgnoredParams['sort'])) {
			if (isset($_GET['nav']['sort'])) {
				//$sPostLink .= '/'.$_GET['nav']['sort'];//$this->_sAfterLink;
				$sPreLink .= '/'.$_GET['nav']['sort'].'';//$this->_sAfterLink;
			} elseif (isset($_SESSION[APP_TYPE][$_GET['ctrl']][$_GET['act']]['sort'])) {
				$sPreLink .= '/'.$_SESSION[APP_TYPE][$_GET['ctrl']][$_GET['act']]['sort'].'';
			}
		}
		// ewentualne ignorowanie parametrow listingu z poziomu kolekcji
		if (!isset($this->_aIgnoredParams['all']) && !isset($this->_aIgnoredParams['sort_dir'])) {
			if (isset($_GET['nav']['sort_dir'])) {
				//$sPostLink .= '/'.$_GET['nav']['sort_dir'];//$this->_sAfterLink;
				$sPreLink .= '/'.$_GET['nav']['sort_dir'].'';//$this->_sAfterLink;
			} elseif (isset($_SESSION[APP_TYPE][$_GET['ctrl']][$_GET['act']]['sort_dir'])) {
				$sPreLink .= '/'.$_SESSION[APP_TYPE][$_GET['ctrl']][$_GET['act']]['sort_dir'].'';
			}
		}
		
		//if (!isset($this->_aIgnoredParams['all']) && !isset($this->_aIgnoredParams['sort_dir'])) {
		$sPreLink .= '/page/';
//		if (!isset($this->_aIgnoredParams['all']) && !isset($this->_aIgnoredParams['page'])) {
		if (isset($_GET['nav']['page'])) {
			$iStart = $_GET['nav']['page'];
			$_SESSION[APP_TYPE][$_GET['ctrl']][$_GET['act']]['page'] = $_GET['nav']['page'];
		} elseif (isset($_SESSION[APP_TYPE][$_GET['ctrl']][$_GET['act']]['page'])) {
			$iStart = $_SESSION[APP_TYPE][$_GET['ctrl']][$_GET['act']]['page'];
		} else {
			$iStart = 1;
		}
//		}
//		$this

//		if (!isset($where)) {
//			$where = '';
//		}
		if ($this->_iTotal > $this->_iPageSize || $this->_aNavDetails) {
			$this->_sPagination = '<ul class="fr links pagination">';

			//$this->_sPagination .= '<li><span>TOTAL: '.$this->_iTotal.'</span></li>';
			
			$iPages = ceil($this->_iTotal/$this->_iPageSize);
			
//			$this->_sPagination .= '<li><span>, pages: '.$iPages.'</span></li>';
			
//			echo '_'.$iPages.'_'.$this->_iTotal.'_'.$this->_iPageSize;
	
			if ($iStart > 1 && in_array('prev', $this->_aMode)) {
//				if (in_array('reverse', $this->_aMode)) {
//					if ($iPages > ($iStart+1) && in_array('next', $this->_aMode)) {
//						$this->_sPagination .= '<li><a href="'.$sPreLink.($iStart+1).$sPostLink.'">'.$prev.'</a></li>';
//					}
//				} else {
					//$this->_sPagination .= '<li><a href="'.$sPreLink.($iStart-1).$sPostLink.'">'.$this->_sPrevLabel.'</a></li>';
					$this->_sPagination .= '<li><a href="'.$sPreLink.($iStart-1).$sPostLink.'">'.$this->_sPrevLabel.'</a></li>';
//				}
			}
	
			if (in_array('archive', $this->_aMode)) {
				if ($iPages > 0) {
					for ($iPage = 1; $iPage <= $iPages; $iPage++) {
		//				if ($iPage < 3 || $iPage >= $iPages-3 || ($iPage >= ($iStart/$this->_iPageSize)-1 && $iPage <= ($iStart/$this->_iPageSize)+1)) {
//						if ($iPage < 3 || $iPage >= $iPages-3 || ($iPage >= $iStart-1 && $iPage <= $iStart+1)) {
						if ($iPage < Paginator::PAGINATOR_PAGES_LIMIT || $iPage >= $iPages-Paginator::PAGINATOR_PAGES_LIMIT || ($iPage >= $iStart-1 && $iPage <= $iStart+1)) {
//						if ($iPage < Paginator::PAGINATOR_PAGES_LIMIT+1 || $iPage >= $iPages-Paginator::PAGINATOR_PAGES_LIMIT || ($iPage >= $iStart && $iPage <= $iStart)) {
							if ($iPage == $iStart) {
//								$this->_sPagination .= '<li><a class="active">'.($iPage+1).'</a></li>';
								$this->_sPagination .= '<li><a href="'.$sPreLink.($iPage).$sPostLink.'" class="active">'.($iPage).'</a></li>';
							} else {
		//						$this->_sPagination .= '<li><a href="'.$sPreLink.($iPage*$this->_iPageSize).$sPostLink.'">'.($iPage+1).'</a></li>';
//								$this->_sPagination .= '<li><a href="'.$sPreLink.($iPage).$sPostLink.'">'.($iPage+1).'</a></li>';
								$this->_sPagination .= '<li><a href="'.$sPreLink.($iPage).$sPostLink.'">'.($iPage).'</a></li>';
							}
						} else {
							$this->_sPagination .= '%';
						}
					}
				}
			}
			
			if (in_array('all', $this->_aMode)) {
				if ($iPages > 0) {
					for ($iPage = 1; $iPage <= $iPages; $iPage++) {
						if ($iPage == $iStart) {
//							$this->_sPagination .= '<li><a class="active">'.($iPage+1).'</a></li>';
							$this->_sPagination .= '<li><a href="'.$sPreLink.($iPage).$sPostLink.'" class="active">'.($iPage).'</a></li>';
						} else {
//							$this->_sPagination .= '<li><a href="'.$sPreLink.($iPage).$sPostLink.'">'.($iPage+1).'</a></li>';
							$this->_sPagination .= '<li><a href="'.$sPreLink.($iPage).$sPostLink.'">'.($iPage).'</a></li>';
						}
					}
				}
			}
	
//			if($this->_iTotal > $iStart + $this->_iPageSize && ($this->_sMode == 'prev-next' || $this->_sMode == 'full')) {
			if ($iPages > ($iStart) && in_array('next', $this->_aMode)) {
//				if (in_array('reverse', $this->_aMode)) {
//					if ($iStart > 0 && in_array('prev', $this->_aMode)) {
//						$this->_sPagination .= '<li><a href="'.$sPreLink.($iStart-1).$sPostLink.'">'.$next.'</a></li>';
//					}
//				} else {
//					$this->_sPagination .= '<li>_'.$iPages.'_<a href="'.$sPreLink.($iStart+1).$sPostLink.'">'.$this->_sNextLabel.'</a></li>';
					$this->_sPagination .= '<li><a href="'.$sPreLink.($iStart+1).$sPostLink.'">'.$this->_sNextLabel.'</a></li>';
//				}
			}
			
			if (in_array('size', $this->_aMode) || in_array('all', $this->_aMode)) {
				if ($iPages > 0) {
					$this->_sPagination .= '<select>';
					for ($iPage = 1; $iPage <= $iPages; $iPage++) {
						if ($iPage == $iStart) {
//							$this->_sPagination .= '<li><a class="active">'.($iPage+1).'</a></li>';
							$this->_sPagination .= '<option value="'.$sPreLink.($iPage).$sPostLink.'" selsected="selected">'.($iPage).'</option>';
						} else {
//							$this->_sPagination .= '<li><a href="'.$sPreLink.($iPage).$sPostLink.'">'.($iPage+1).'</a></li>';
							$this->_sPagination .= '<option value="'.$sPreLink.($iPage).$sPostLink.'">'.($iPage).'</option>';
						}
					}
					$this->_sPagination .= '</select>';
				}
			}
			
			if (in_array('details', $this->_aMode)) {
				$aFirst = current($this->_aNavDetails);
				$aLast = end($this->_aNavDetails);
				reset($this->_aNavDetails);
				
				if (isset($_GET['nav']['alias'])) {
					if ($aFirst['alias'] == $_GET['nav']['alias']) {
						$aNext = next($this->_aNavDetails);
						$this->_sPagination .= '<li><a href="'.$sPreLink.$aNext['alias'].$sPostLink.'">'.$aNext['title'].'</a></li>';
					} elseif ($aLast['alias'] == $_GET['nav']['alias']) {
						end($this->_aNavDetails);
						$aPrev = prev($this->_aNavDetails);
						$this->_sPagination .= '<li><a href="'.$sPreLink.$aPrev['alias'].$sPostLink.'">'.$aPrev['title'].'</a></li>';
					} else {
						foreach ($this->_aNavDetails as $entries => $entry) {
							if ($entries == $_GET['nav']['alias']) {
								prev($this->_aNavDetails);
								$aPrev = prev($this->_aNavDetails);
								
								next($this->_aNavDetails);
								$aNext = next($this->_aNavDetails);
							}
							/*if ($entry['id_entry'] == $_GET['nav']['id']) {
								prev($this->_aNavDetails);
								$aPrev = prev($this->_aNavDetails);
								
								next($this->_aNavDetails);
								$aNext = next($this->_aNavDetails);
							}*/
						}
	
						$this->_sPagination .= '<li><a href="'.$sPreLink.$aPrev['alias'].$sPostLink.'" title="prev">'.$aPrev['title'].'</a></li>';
						$this->_sPagination .= '<li><a href="'.$sPreLink.$aNext['alias'].$sPostLink.'" title="next">'.$aNext['title'].'</a></li>';
					}
				}
			}
			
			$this->_sPagination = preg_replace('/[%]+/', '<li><span class="separator">...</span></li>', $this->_sPagination);
			$this->_sPagination .= '</ul>';
			return $this->_sPagination;
		} else {
			return '';
		}
	}
	
	public function render() {
	    return 'render() method output';
	}
}
?>
