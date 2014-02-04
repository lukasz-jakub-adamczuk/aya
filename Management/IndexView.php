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

		Debug::show(Navigator::getOwner());
		
		Navigator::init();

		Debug::show(Navigator::load(Navigator::getOwner()));

		// Debug::show($_SESSION['_nav_'], '$_SESSION');

		// $_SESSION['test'] = 'aaa';

		// print_r($_SESSION);

		
		// search field condition
		$sSearch = isset($_REQUEST['nav']['search']) ? $_REQUEST['nav']['search'] : null;
		
		// $sWhere = isset($_REQUEST['nav']['id_offer']) ? $_REQUEST['nav']['id_article'] : null;
		
		$sCtrl = $_GET['ctrl'];
		// $sAct = ($_GET['act'] == 'insert' || $_GET['act'] == 'update') ? 'index' : $_GET['act'];
		$sAct = $_GET['act'];

		// echo $this->_sDaoName;
		
		// kolekcja
		$oIndexCollection = Dao::collection($this->_sDaoName, $this->_sDaoName.'-'.$sCtrl.'-'.$sAct);

		
		$sLowerDashCtrlName = str_replace('_', '-', $this->_sDaoIndex);
		

		$oIndexCollection->setGroupPart(' GROUP BY article.id_article');
		// $oIndexCollection->setOrderPart(' ORDER BY '.$_GET['nav']['sort'].' DESC');

		if ($sSearch) {
			// $oIndexCollection->search('');
		}
		
		// $oIndexCollection->navDefault('sort', 'creation-date');
		// $oIndexCollection->navDefault('order', 'desc');

		// $oIndexCollection->navDefault('size', 25);


		
		
		// get records
		$oIndexCollection->load();
		
		$aFilters = $this->_getFilters();
		
		$aNavigator = $oIndexCollection->getNavigator();
		foreach ($aFilters as $name => $filter) {
			if (isset($aNavigator[$name])) {
				$aFilters[$name]['selected'] = $aNavigator[$name];
			}
		}
		
		$this->_oRenderer->assign('aFilters', $aFilters);


		$oCategories = Dao::collection('category');
		// $oCategories->select('SELECT * ');
		$oCategories->orderby('name');
		$oCategories->load(-1);
		// print_r($oCategories->getRows());
		$this->_oRenderer->assign('aCategories', $oCategories->getRows());
		
		
		require_once __DIR__ . '/../../XhtmlTable/Aya/Yaml/AyaYamlLoader.php';
		
		$file = APP_DIR . '/conf/layout/tables/'.$sLowerDashCtrlName.'.yml';
		$aConfig = AyaYamlLoader::parse($file);

		
		$oAyaXhtmlTable = new AyaXhtmlTable();
		
//        $oAyaXhtmlTable->setCacheDir(APP_DIR.DS.'tmp');

		$oAyaXhtmlTable->setSortLink(BASE_URL.'/'.$sLowerDashCtrlName);
		$oAyaXhtmlTable->setBaseLink(BASE_URL);
		
		$oAyaXhtmlTable->configure($aConfig);
		
		$file = APP_DIR . '/langs/pl/tables/common.yml';
		$aGlobalTexts = AyaYamlLoader::parse($file);
		
		$file = APP_DIR . '/langs/pl/tables/'.$sLowerDashCtrlName.'.yml';
		$aLocalTexts = AyaYamlLoader::parse($file);
		
		$oAyaXhtmlTable->translate($aGlobalTexts, $aLocalTexts);
		
		$oAyaXhtmlTable->assign($oIndexCollection->getRows(), $oIndexCollection->getNavigator());

		// echo $oIndexCollection->getPaginator('archive');

		// BASE_URL.'/'.
		$aNavigator = $oIndexCollection->getNavigator();
		
		$oPaginator = new Paginator($aNavigator);
		$sPaginator = $oPaginator->configure('archive', BASE_URL.'/'.$sLowerDashCtrlName)->generate();

		// ostateczne wyslanie danych do szablonu
		$this->_oRenderer->assign('sTable', $oAyaXhtmlTable->render());
		$this->_oRenderer->assign('sPaginator', $sPaginator);
		$this->_oRenderer->assign('aNavigator', $aNavigator);
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
