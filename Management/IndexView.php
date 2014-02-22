<?php
require_once AYA_DIR.'/Core/View.php';
require_once AYA_DIR.'/Core/Dao.php';

//require_once AYA_DIR.'/Xhtml/Table/AyaXhtmlTable.php';
require_once AYA_DIR.'/../XhtmlTable/Aya/Xhtml/Table/AyaXhtmlTable.php';

class IndexView extends View {

	protected $_sOwner;

	protected function setCollectionOwner() {
		
	}

	protected function _getFilters() {
		return false;
	}
	
	public function fill() {
		Debug::show(Navigator::getOwner(), 'navigator owner');
		
		Navigator::init();

		Debug::show(Navigator::load(Navigator::getOwner()), 'navigator owner');

		
		// search field condition
		$sSearch = isset($_REQUEST['nav']['search']) ? $_REQUEST['nav']['search'] : null;
		
		// $sWhere = isset($_REQUEST['nav']['id_offer']) ? $_REQUEST['nav']['id_article'] : null;
		
		// maybe need now or in the future
		$sCtrl = $_GET['ctrl'];
		$sAct = ($_GET['act'] == 'insert' || $_GET['act'] == 'update') ? 'index' : $_GET['act'];

		// echo $this->_sDaoName;
		
		// kolekcja
		$oIndexCollection = Dao::collection($this->_sDaoName, $this->_sOwner);


		$sLowerDashCtrlName = str_replace('_', '-', $this->_sDaoIndex);
		

		$oIndexCollection->setGroupPart(' GROUP BY '.$this->_sDaoIndex.'.id_'.$this->_sDaoIndex);
		// $oIndexCollection->setOrderPart(' ORDER BY '.$_GET['nav']['sort'].' DESC');

		if ($sSearch) {
			// $oIndexCollection->search('');
		}
		
		$oIndexCollection->navDefault('sort', 'creation-date');
		$oIndexCollection->navDefault('order', 'desc');

		// $oIndexCollection->navDefault('size', 25);

		// $oIndexCollection->orderby('creation_date', 'desc');
		
		
		// get records
		$oIndexCollection->load(20);
		

		$aFilters = $this->_getFilters();
		
		$aNavigator = $oIndexCollection->getNavigator();
		if ($aFilters) {
			foreach ($aFilters as $name => $filter) {
				if (isset($aNavigator[$name])) {
					$aFilters[$name]['selected'] = $aNavigator[$name];
				}
			}
		}
		
		$this->_oRenderer->assign('aFilters', $aFilters);


		$aRows = $oIndexCollection->getRows();
		
		
		
		// table configuration
		require_once __DIR__ . '/../../XhtmlTable/Aya/Yaml/AyaYamlLoader.php';
		
		$file = APP_DIR . '/conf/layout/tables/'.$sLowerDashCtrlName.'.yml';
		if (file_exists($file)) {
			$aConfig = AyaYamlLoader::parse($file);
		} else {
			if (is_array($aRows) && count($aRows) > 0) {
				$aDefaultConfig = array_keys(current($aRows));
				$aConfig = array('cols' => array_flip($aDefaultConfig));
			}
		}

		
		$oAyaXhtmlTable = new AyaXhtmlTable();
		
		// $oAyaXhtmlTable->setCacheDir(APP_DIR.DS.'tmp');

		$oAyaXhtmlTable->setSortLink(BASE_URL.'/'.$sLowerDashCtrlName);
		$oAyaXhtmlTable->setBaseLink(BASE_URL);
		
		$oAyaXhtmlTable->configure($aConfig);
		
		// global texts are available always
		$filename = APP_DIR . '/langs/pl/tables/common.yml';
		$aGlobalTexts = AyaYamlLoader::parse($filename);
		
		// local texts are available conditionally
		$filename = APP_DIR . '/langs/pl/tables/'.$sLowerDashCtrlName.'.yml';
		if (file_exists($filename)) {
			$aLocalTexts = AyaYamlLoader::parse($filename);
		
			$oAyaXhtmlTable->translate($aGlobalTexts, $aLocalTexts);
		}
		
		$oAyaXhtmlTable->assign($oIndexCollection->getRows(), $oIndexCollection->getNavigator());


		$aNavigator = $oIndexCollection->getNavigator();

		// pagination		
		$oPaginator = new Paginator($aNavigator);
		$sPaginator = $oPaginator->configure('archive', BASE_URL.'/'.$sLowerDashCtrlName)->generate();

		// data transfer to templates
		$this->_oRenderer->assign('sTable', $oAyaXhtmlTable->render());
		$this->_oRenderer->assign('sPaginator', $sPaginator);
		$this->_oRenderer->assign('aNavigator', $aNavigator);
	}
	
	public function beforeFill() {
		// inheritance
		$this->_sOwner = $this->_sDaoName.'-'.$_GET['ctrl'].'-'.$_GET['act'];

		Navigator::setOwner($this->_sOwner);
	}
	
	public function afterFill() {
		// inheritance
	}
}