<?php
require_once AYA_DIR.'/Core/View.php';

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
		$sCtrl = str_replace('_', '-', $this->_sDaoIndex);
		$sAct = ($_GET['act'] == 'insert' || $_GET['act'] == 'update') ? 'index' : $_GET['act'];

		// sql cache
		// $sSqlCacheFile = TMP_DIR . '/sql/collection/'.$sCtrl.'-'.$sAct.'';
		$sSqlCacheFile = TMP_DIR . '/'.$sCtrl.'-'.$sAct.'';
		
		Time::start('sql-collection');


		// index collection
		$oIndexCollection = Dao::collection($this->_sDaoName, $this->_sOwner);

		$oIndexCollection->setGroupPart(' GROUP BY '.$this->_sDaoIndex.'.id_'.$this->_sDaoIndex);
		// $oIndexCollection->setOrderPart(' ORDER BY '.$_GET['nav']['sort'].' DESC');

		if ($sSearch) {
			// $oIndexCollection->search('');
		}
			
		$oIndexCollection->navDefault('sort', 'creation-date');
		$oIndexCollection->navDefault('order', 'desc');
		
		if (!file_exists($sSqlCacheFile)) {
			$aRows = unserialize(file_get_contents($sSqlCacheFile));
		} else {
			// get records
			$oIndexCollection->load(20);

			$aRows = $oIndexCollection->getRows();
			
			file_put_contents($sSqlCacheFile, serialize($aRows));
		}

		Time::stop('sql-collection');
		

		// filters
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
		
		
		// table configuration
		require_once __DIR__ . '/../../XhtmlTable/Aya/Yaml/AyaYamlLoader.php';
		Time::stop('yaml-loader');
		
		$file = APP_DIR . '/conf/layout/tables/'.$sCtrl.'.yml';
		if (file_exists($file)) {
			Time::start('view-yaml-parsing');
			$aConfig = AyaYamlLoader::parse($file);
			Time::stop('view-yaml-parsing');
		} else {
			if (is_array($aRows) && count($aRows) > 0) {
				$aDefaultConfig = array_keys(current($aRows));
				$aConfig = array('cols' => array_flip($aDefaultConfig));
			}
		}

		$oAyaXhtmlTable = new AyaXhtmlTable();
		
		
		// $oAyaXhtmlTable->setCacheDir(APP_DIR.DS.'tmp');

		$oAyaXhtmlTable->setSortLink(BASE_URL.'/'.$sCtrl);
		$oAyaXhtmlTable->setBaseLink(BASE_URL);
		
		$oAyaXhtmlTable->configure($aConfig);
		
		// global texts are available always
		$filename = APP_DIR . '/langs/pl/tables/common.yml';
		$aGlobalTexts = AyaYamlLoader::parse($filename);
		
		// local texts are available conditionally
		$filename = APP_DIR . '/langs/pl/tables/'.$sCtrl.'.yml';
		if (file_exists($filename)) {
			$aLocalTexts = AyaYamlLoader::parse($filename);
		
			$oAyaXhtmlTable->translate($aGlobalTexts, $aLocalTexts);
		}
		
		$oAyaXhtmlTable->assign($aRows, $oIndexCollection->getNavigator());
		Time::stop('view-html-table-assign');

		$this->_oRenderer->assign('sTable', $oAyaXhtmlTable->render());
		Time::stop('view-html-table');

		// navigator data
		$aNavigator = $oIndexCollection->getNavigator();
		Debug::show($aNavigator, 'nav from collection');
		$this->_oRenderer->assign('aNavigator', $aNavigator);
		Time::stop('get-navigator');

		// pagination		
		$oPaginator = new Paginator($aNavigator);
		$sPaginator = $oPaginator->configure('archive', BASE_URL.'/'.$sCtrl)->generate();
		$this->_oRenderer->assign('sPaginator', $sPaginator);
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