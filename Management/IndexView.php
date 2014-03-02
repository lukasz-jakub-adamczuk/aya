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

	public function defaultOrdering($oIndexCollection) {
		$oIndexCollection->navDefault('sort', 'creation-date');
		$oIndexCollection->navDefault('order', 'desc');
		return $oIndexCollection;
	}

	public function defaultGrouping($oIndexCollection) {
		$oIndexCollection->setGroupPart(' GROUP BY '.$this->_sDaoIndex.'.id_'.$this->_sDaoIndex);
		return $oIndexCollection;
	}
	
	public function fill() {
		Navigator::init();

		Debug::show(Navigator::load(Navigator::getOwner()), 'navigator owner');
		
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

		$oIndexCollection = $this->defaultOrdering($oIndexCollection);
		$oIndexCollection = $this->defaultGrouping($oIndexCollection);	

		
		
		if (file_exists($sSqlCacheFile)) {
			// records in file
			$aData = unserialize(file_get_contents($sSqlCacheFile));

			$aRows = $aData['rows'];
			$aNavigator = $aData['navigator'];

			$oIndexCollection->restore($aRows, $aNavigator);
		} else {
			// records in db
			$oIndexCollection->load(20);

			// TODO nav shouldn't be related to order of execution
			$aRows = $oIndexCollection->getRows();
			$aNavigator = $oIndexCollection->getNavigator();
			
			file_put_contents($sSqlCacheFile, serialize(array('rows' => $aRows, 'navigator' => $aNavigator)));
		}

		Time::stop('sql-collection');
		

		// filters
		$aFilters = $this->_getFilters();
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
		} else {
			$oAyaXhtmlTable->translate($aGlobalTexts);
		}
		
		$oAyaXhtmlTable->assign($aRows, $oIndexCollection->getNavigator());

		$this->_oRenderer->assign('sTable', $oAyaXhtmlTable->render());

		// navigator data
		$aNavigator = $oIndexCollection->getNavigator();
		Debug::show($aNavigator, 'nav from collection');
		$this->_oRenderer->assign('aNavigator', $aNavigator);


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