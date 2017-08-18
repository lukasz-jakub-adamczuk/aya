<?php

namespace Aya\Management;

use Aya\Core\Controller;
use Aya\Core\User;
use Aya\Helper\Breadcrumbs;

// use Ivy\Helper\CommentManager;
// use Ivy\Helper\NavigationManager;
// use Ivy\Helper\PostmanManager;

class FrontController extends Controller {

	public function indexAction() {}

	public function infoAction() {}

	public function beforeAction() {
        parent::beforeAction();
		
        // navigation
		// $this->_renderer->assign('aNavigation', NavigationManager::getNavigation());

		// // Breadcrumbs::add('', 'squarezone.pl', 'icon-home');
		// $aItem = array(
		// 	'name' => 'ctrl',
		// 	'url' => $this->getCtrlName(),
		// 	'text' => $this->getCtrlName(),
		// );
		// Breadcrumbs::add($aItem);

		// // $this->_renderer->assign('ctrl', $this->getCtrlName());
		// // $this->_renderer->assign('act', $this->getActionName());

		// PostmanManager::analyzeFeeds();

		// $this->_renderer->assign('aCounters', PostmanManager::getFeedsCounters());
		// $this->_renderer->assign('iTotal', PostmanManager::getFeedsTotal());

		// // comments
		// CommentManager::analyzeComments();

		// $this->_renderer->assign('iAllComments', CommentManager::getCommentsTotal());
	}
	
	// TODO should name init()
	public function afterAction() {
		parent::afterAction();

		if (User::set()) {
			$this->_renderer->assign('user', User::get());
		}

		$this->_renderer->assign('aBreadcrumbs', Breadcrumbs::get());
		
		// vars in templates
		$this->_renderer->assign('base', BASE_URL);
		if (defined('SITE_URL')) {
			$this->_renderer->assign('site', SITE_URL);
		}
	}
}