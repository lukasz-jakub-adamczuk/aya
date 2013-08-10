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
		
		Navigator::init();
        
		// search field condition
		$sSearch = isset($_REQUEST['nav']['search']) ? $_REQUEST['nav']['search'] : null;
		
		$sWhere = isset($_REQUEST['nav']['id_offer']) ? $_REQUEST['nav']['id_offer'] : null;
		
		
		// kolekcja
        $oIndexCollection = Dao::collection($this->_sDaoName, $this->_sDaoName.'-'.$_GET['ctrl'].'-'.$_GET['act']);
        
        
		$sLowerDashCtrlName = str_replace('_', '-', $this->_sDaoIndex);
		
		
		
		$oIndexCollection->navDefault('sort', 'idx');
		$oIndexCollection->navDefault('order', 'asc');
	
		
        
        
        // get records
        $oIndexCollection->get();
        
        
        
        $aFilters = $this->_getFilters();
        
        $aNavigator = $oIndexCollection->getNavigator();
        foreach ($aFilters as $name => $filter) {
            if (isset($aNavigator[$name])) {
                $aFilters[$name]['selected'] = $aNavigator[$name];
            }
        }
        
        $this->_oRenderer->assign('aFilters', $aFilters);
        
        
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
