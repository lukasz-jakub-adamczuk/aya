<?php
require_once AYA_DIR.'/Core/View.php';
require_once AYA_DIR.'/Core/Dao.php';

//require_once AYA_DIR.'/Xhtml/Table/AyaXhtmlTable.php';
require_once AYA_DIR.'/../XhtmlTable/Aya/Xhtml/Table/AyaXhtmlTable.php';

/**
 * abstrakcyjna klasa widoku
 * 
 * @author ash
 *
 */
class IndexView extends View {

    protected function _getFilters() {
        return false;
    }
	
	/**
	 * podstawowe dzialania widoku
	 * 
	 * @return unknown_type
	 */
	public function fill() {
		// startowe dzialanie
    	$this->_runBeforeFill();
		
		// ustawienie numeru aktualnej strony dla kolekcji
		
        // TODO move to Navigator class
        if (isset($_POST['nav'])) {
            foreach ($_POST['nav'] as $key => $val) {
                $_SESSION['_nav_'][$_GET['ctrl']][$_GET['act']][$key] = $val;
            }
        }
        
		// podstawowe dzialanie widoku
		if (isset($_GET['nav']['size'])) {
			$iSize = $_GET['nav']['size'];
			$_SESSION[$_GET['ctrl']][$_GET['act']]['size'] = $_GET['nav']['size'];
		}
		
		if (isset($_GET['nav']['page'])) {
			$iPage = $_GET['nav']['page'];
			$_SESSION[$_GET['ctrl']][$_GET['act']]['page'] = $_GET['nav']['page'];
		} elseif (isset($_SESSION[$_GET['ctrl']][$_GET['act']]['page'])) {
			// jesli istnieje zapamietana strona
			// to moze byc dla niej wynikow w przypadku wyszukiwania
			// dlatego zawsze ustawiana jest pierwsza strona wynikow
			if (isset($_POST['nav']['search'])) {
				$iPage = $_SESSION[$_GET['ctrl']][$_GET['act']]['page'] = 1;
			} else {
				$iPage = $_SESSION[$_GET['ctrl']][$_GET['act']]['page'];
			}
		} else {
			$iPage = 1;
		}
		$sSearch = isset($_REQUEST['nav']['search']) ? $_REQUEST['nav']['search'] : null;
		
		$sWhere = isset($_REQUEST['nav']['id_offer']) ? $_REQUEST['nav']['id_offer'] : null;
		
		
		
//		print_r($_SESSION);
		
		
		if ($sWhere) {
			$_SESSION['where'] = $sWhere;
		}
		
	
		// pseudo reset filtra
		if (isset($_REQUEST['nav']['id_offer']) && empty($_REQUEST['nav']['id_offer'])) {
			$_SESSION['where'] = '';
		}
		
		if (isset($_SESSION['where'])) {
			$sWhere = $_SESSION['where'];
		}
		
		//echo $this->_sDaoName;
		//echo $this->_sDaoIndex;
		
		
		// kolekcja
        $oIndexCollection = Dao::collection($this->_sDaoName);
        
        
        //$oIndexCollection->limit(7);
        
        $aFilters = $this->_getFilters();
        
//        print_r($aFilters);
        
        foreach ($aFilters as $name => $filter) {
            if (isset($_SESSION['_nav_'][$_GET['ctrl']][$_GET['act']][$name])) {
                if ($name == 'search' && $_SESSION['_nav_'][$_GET['ctrl']][$_GET['act']][$name] != '') {
                    $oIndexCollection->search($this->_sDaoIndex.'.`title`', $sSearch);
                } else {
                    $aFilters[$name]['selected'] = $_SESSION['_nav_'][$_GET['ctrl']][$_GET['act']][$name];
                    if (substr($name, 0, 3) == 'id_') {
                        $oIndexCollection->navSet($this->_sDaoIndex.'.'.$name, $_SESSION['_nav_'][$_GET['ctrl']][$_GET['act']][$name]);
                    } else {
                        $oIndexCollection->navSet($name, $_SESSION['_nav_'][$_GET['ctrl']][$_GET['act']][$name]);
                    }
                }
            }
        }
        /*
        foreach ($aFilters['visible']['options'] as $fok => $fo) {
            if ($fok === 'null') {
                echo 'null';
            } else {
                echo 'rozny od null';
            }
        }
        */
        
        $this->_oRenderer->assign('aFilters', $aFilters);
        
		$sLowerDashCtrlName = str_replace('_', '-', $this->_sDaoIndex);
		
		
		$oIndexCollection->navDefault('sort', 'idx');
		$oIndexCollection->navDefault('order', 'asc');
		
        
        if (isset($iSize)) {
            $oIndexCollection->setPageSize($iSize);
        }

        
        $oIndexCollection->get($iPage);
        
        
        
        require_once __DIR__ . '/../../XhtmlTable/Aya/Yaml/AyaYamlLoader.php';
        
        $file = APP_DIR . '/conf/layout/tables/'.$sLowerDashCtrlName.'.yml';
        $aConfig = AyaYamlLoader::parse($file);

        
        $oAyaXhtmlTable = new AyaXhtmlTable();
		
//        $oAyaXhtmlTable->setCacheDir(APP_DIR.DS.'tmp');

        $oAyaXhtmlTable->setSortLink('/'.$sLowerDashCtrlName);
		
        $oAyaXhtmlTable->configure($aConfig);
        
        $file = APP_DIR . '/langs/pl/tables/common.yml';
        $aGlobalTexts = AyaYamlLoader::parse($file);
        
        $file = APP_DIR . '/langs/pl/tables/'.$sLowerDashCtrlName.'.yml';
        $aLocalTexts = AyaYamlLoader::parse($file);
        
        $oAyaXhtmlTable->translate($aGlobalTexts, $aLocalTexts);
        
        $oAyaXhtmlTable->assign($oIndexCollection->getRows(), $oIndexCollection->getNavigator());

		// ostateczne wyslanie danych do szablonu
		$this->_oRenderer->assign('sTable', $oAyaXhtmlTable->render());
		$this->_oRenderer->assign('sPaginator', $oIndexCollection->getPaginator('archive'));
		$this->_oRenderer->assign('aNavigator', $oIndexCollection->getNavigator());
	}
	
	protected function _runBeforeFill() {
	    // dla potomnych
	    Navigator::setOwner($this->_sDaoName.'-'.$_GET['ctrl'].'-'.$_GET['act']);
	}
	
	protected function _runAfterFill() {
	    // dla potomnych
	}
}
?>

