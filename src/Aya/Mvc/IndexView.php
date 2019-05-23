<?php

namespace Aya\Mvc;

use Aya\Core\Dao;
use Aya\Core\Debug;
use Aya\Core\View;
use Aya\Core\Navigator;
use Aya\Dao\Paginator;
use Aya\Helper\Time;

use Ivy\Helper\RelatedActions;

use Symfony\Component\Yaml\Yaml;

class IndexView extends View {

    protected $_oCollection;

    protected $_sOwner;

    protected $_iCollectionSize;

    protected function setCollectionOwner() {
        // collection owner
    }

    // protected function setCollectionSize($iSize) {
    //     $this->_iCollectionSize = $iSize;
    // }

    protected function _getSections() {
        return false;
    }

    protected function _getMassActions() {
        return false;
    }

    protected function _getRelatedActions() {
        return RelatedActions::getActions(array('refresh', 'add'));
    }

    protected function _getFilters() {
        return false;
    }

    protected function _getSearchFields() {
        return array('title');
    }

    public function defaultOrdering() {
        $this->_oCollection->navDefault('sort', 'creation-date');
        $this->_oCollection->navDefault('order', 'desc');
    }

    public function defaultGrouping() {
        $this->_oCollection->setGroupPart(' GROUP BY '.$this->_sDaoIndex.'.id_'.$this->_sDaoIndex);
    }

    // public function defaultSearching($this->_oCollection) {
    //     $this->_oCollection->setSearchFields('title');
    //     return $this->_oCollection;
    // }

    public function postProcessDataset($aRows) {
        return $aRows;
    }

    public function init() {
        $this->_sOwner = $this->_sDaoName.'-'.$_GET['ctrl'].'-'.$_GET['act'];
        $this->_iCollectionSize = 20;

        Navigator::setOwner($this->_sOwner);

        $this->_renderer->assign('sections', $this->_getSections());
        $this->_renderer->assign('massActions', $this->_getMassActions());
        $this->_renderer->assign('relatedActions', $this->_getRelatedActions());
    }
    
    public function fill() {
        $this->_handleCollection();

        $aRows = $this->_oCollection->getRows();

        $aRows = $this->postProcessDataset($aRows);

        $this->_handleFilters();
        
        $this->_handleDataset($aRows);

        $this->_handleNavigator();

        $this->_handlePaginator();
    }
    
    public function beforeFill() {
        // inheritance
    }
    
    public function afterFill() {
        // inheritance
    }

    // PRIVATE METHODS FOR HANDLING FILL

    protected function _handleCollection() {
        $bUseCache = false;

        Navigator::init();

        Debug::show(Navigator::getOwner(), 'nav owener is set');

        Debug::show(Navigator::load(Navigator::getOwner()), 'navigator values');

        if (Navigator::is('search')) {
            $bUseCache = false;
        }
        
        $sAct = ($_GET['act'] == 'insert' || $_GET['act'] == 'update') ? 'index' : $_GET['act'];

        $sSqlCacheFile = TMP_DIR . '/sql/collection/'.$_GET['ctrl'].'-'.$sAct.'';
        
        Time::start('sql-collection');

        // index collection
        $aParams = [];
        $aParams['search'] = $this->_getSearchFields();

        Debug::show(Navigator::getOwner(), 'nav owner is set');
        Debug::show($this->_sOwner, 'nav owner is set');
        
        $this->_oCollection = Dao::collection($this->_sDaoName, $this->_sOwner, $aParams);

        $this->defaultOrdering();
        $this->defaultGrouping();

        Debug::show(Navigator::load($this->_sOwner));
        Debug::show($_SESSION['_nav_']);

        // $this->_oCollection = $this->defaultSearching($this->_oCollection);

        // Debug::show($this->_getSearchFields());

        $this->_oCollection->init();
        
        if ($bUseCache && file_exists($sSqlCacheFile)) {
            Debug::show($sSqlCacheFile, 'collection from cache file', 'info');
            // records in file
            $aData = unserialize(file_get_contents($sSqlCacheFile));

            $aRows = $aData['rows'];
            $aNavigator = $aData['navigator'];

            $this->_oCollection->restore($aRows, $aNavigator);
        } else {
            // create cache location directory
            $sSqlCacheDir = dirname($sSqlCacheFile);
            if (!file_exists($sSqlCacheDir)) {
                mkdir($sSqlCacheDir, 0777, true);
            }
            
            // records in db
            $this->_oCollection->load($this->_iCollectionSize);

            // TODO nav shouldn't be related to order of execution
            $aRows = $this->_oCollection->getRows();
            $aNavigator = $this->_oCollection->getNavigator();
            
            file_put_contents($sSqlCacheFile, serialize(array('rows' => $aRows, 'navigator' => $aNavigator)));
        }

        // print_r($this->_oCollection);

        Time::stop('sql-collection');
    }

    protected function _handleFilters() {
        // filters
        $aFilters = $this->_getFilters();
        $aNavigator = $this->_oCollection->getNavigator();
        if ($aFilters) {
            foreach ($aFilters as $name => $filter) {
                if (isset($aNavigator[$name])) {
                    $aFilters[$name]['selected'] = $aNavigator[$name];
                }
            }
        }
        
        $this->_renderer->assign('aFilters', $aFilters);
    }

    protected function _handleDataset($aRows) {
        // table configuration
        // echo 'path: ' . dirname(ROOT_DIR) . '/XhtmlTable/Aya/Yaml/AyaYamlLoader.php';
        require_once dirname(ROOT_DIR) . '/XhtmlTable/Aya/Yaml/AyaYamlLoader.php';
        Time::stop('yaml-loader');
        
        $file = APP_DIR . '/conf/layout/tables/'.$_GET['ctrl'].'.yml';
        if (file_exists($file)) {
            Time::start('view-yaml-parsing');
            $aConfig = Yaml::parse(file_get_contents($file));
            Time::stop('view-yaml-parsing');
        } else {
            if (is_array($aRows) && count($aRows) > 0) {
                $aDefaultConfig = array_keys(current($aRows));
                $aConfig = array('cols' => array_flip($aDefaultConfig));
            }
        }

        // change config if necessary
        if ($this->_getMassActions() == false) {
            if (isset($aConfig['cols']['id'])) {
                unset($aConfig['cols']['id']);
            }
        }

        require_once dirname(ROOT_DIR) . '/XhtmlTable/Aya/Xhtml/Table/AyaXhtmlTable.php';

        $oAyaXhtmlTable = new \AyaXhtmlTable();
        
        
        // $oAyaXhtmlTable->setCacheDir(APP_DIR.DS.'tmp');

        $oAyaXhtmlTable->setSortLink(BASE_URL.'/'.$_GET['ctrl']);
        $oAyaXhtmlTable->setBaseLink(BASE_URL);
        
        $oAyaXhtmlTable->configure($aConfig);
        
        // global texts are available always
        $filename = APP_DIR . '/langs/pl/tables/common.yml';
        $aGlobalTexts = \AyaYamlLoader::parse($filename);
        
        // local texts are available conditionally
        $filename = APP_DIR . '/langs/pl/tables/'.$_GET['ctrl'].'.yml';
        if (file_exists($filename)) {
            $aLocalTexts = \AyaYamlLoader::parse($filename);
        
            $oAyaXhtmlTable->translate($aGlobalTexts, $aLocalTexts);
        } else {
            $oAyaXhtmlTable->translate($aGlobalTexts);
        }
        // print_r($aLocalTexts);
        // print_r($oAyaXhtmlTable);

        // print_r(current($aRows));
        
        $oAyaXhtmlTable->assign($aRows, $this->_oCollection->getNavigator());

        $this->_renderer->assign('sTable', $oAyaXhtmlTable->render());
    }

    protected function _handleNavigator() {
        // $aNavigator = $this->_oCollection->getNavigator();
        Debug::show($this->_oCollection->getNavigator(), 'nav from collection');
        $this->_renderer->assign('aNavigator', $this->_oCollection->getNavigator());
    }

    protected function _handlePaginator() {
        // print_r($this->_oCollection->getNavigator());
        $oPaginator = new Paginator($this->_oCollection->getNavigator());
        
        // if theme bootstrap
        if (strpos(THEME_DIR, 'andromeda') !== false) {
            $aOptions = array(
                'outer-wrapper' => 'nav',
                'inner-wrapper' => 'ul',
                'inner-wrapper-class' => 'pagination justify-content-end',
                'page-item' => 'li',
                'page-item-class' => 'page-item',
                'page-link-class' => 'page-link',
                'active-element' => 'li',
                'active-element-class' => 'active'
            );
            $oPaginator->setOptions($aOptions);
        }
        if (strpos(THEME_DIR, 'sb-admin-2') !== false) {
            $aOptions = array(
                'outer-wrapper' => 'nav',
                'inner-wrapper' => 'ul',
                'inner-wrapper-class' => 'pagination justify-content-end',
                'page-item' => 'li',
                'page-item-class' => 'paginate_button page-item',
                'page-link-class' => 'page-link',
                'active-element' => 'li',
                'active-element-class' => 'active'
            );
            $oPaginator->setOptions($aOptions);
        }

        $sPaginator = $oPaginator->configure('archive', BASE_URL.'/'.$_GET['ctrl'])->generate();
        $this->_renderer->assign('sPaginator', $sPaginator);
    }
}