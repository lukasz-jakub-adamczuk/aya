<?php

namespace Aya\Dao;

class Paginator {
    
    const PAGINATOR_PAGES_LIMIT = 3;

    const PAGINATOR_PAGE_SIZE = 20;
    
    private $_pagination;
    
    private $_preLink;
    
    private $_postLink;
    
    private $_prevLabel = 'prev';
    
    private $_nextLabel = 'next';
    
    private $_mode;
    
    private $_page = 1;
    
    private $_size;
    
    private $_total;

    private $_config = array();
    
    public function __construct($aNavigator) {
        // total items
        if (isset($aNavigator['total'])) {
            $this->_total = $aNavigator['total'];
        }
        // current page
        if (isset($aNavigator['page'])) {
            $this->_page = $aNavigator['page'];
        } else {
            // $this->_page = $aNavigator['page'];
        }
        // items per side
        if (isset($aNavigator['size'])) {
            $this->_pageSize = $aNavigator['size'];
        } else {
            $this->_pageSize = self::PAGINATOR_PAGE_SIZE;
        }

        // config
        $this->_config = array(
            'outer-wrapper' => null,
            'outer-wrapper-class' => null,
            'inner-wrapper' => 'ul',
            'inner-wrapper-class' => 'paginator',
            'page-item' => 'li',
            'page-item-class' => null,
            'page-link' => 'a', // if not a then href can't be used
            'page-link-class' => null,
            'active-element' => 'a',
            'active-element-class' => 'active'
        );
    }

    public function setOptions($options) {
        foreach ($options as $key => $value) {
            $this->_config[$key] = $value;
        }
    }
    
    public function configure($mode, $preLink = null, $postLink = null) {
        $this->_mode = explode('-', $mode);
        if ($preLink !== null) {
            $this->_preLink = $preLink;
        }
        if ($postLink !== null) {
            $this->_postLink = $postLink;
        }
        
        return $this;
    }
    
    public function generate($prevLabel = null, $nextLabel = null) {
        if ($prevLabel !== null) {
            $this->_prevLabel = $prevLabel;
        }
        if ($nextLabel !== null) {
            $this->_nextLabel = $nextLabel;
        }
        
        
        $preLink = $this->_preLink.'';
        $postLink = '';
        
        
        $preLink .= '/page/';
        
        
        $start = $this->_page;

        if ($this->_total > $this->_pageSize) {
            $this->_pagination = '';
            $this->_pagination .= ($this->_config['outer-wrapper'] ? '<'.$this->_config['outer-wrapper'].'>' : '');
            $this->_pagination .= '<'.$this->_config['inner-wrapper'].($this->_config['inner-wrapper-class'] ? ' class="'.$this->_config['inner-wrapper-class'].'"' : '').'>';

            $pages = ceil($this->_total/$this->_pageSize);
            
            // previous page
            if ($start > 1 && in_array('prev', $this->_mode)) {
                $this->_pagination .= '<li><a href="'.$preLink.($start-1).$postLink.'">'.$this->_prevLabel.'</a></li>';
            }
    
            // archive
            if (in_array('archive', $this->_mode)) {
                if ($pages > 0) {
                    for ($page = 1; $page <= $pages; $page++) {
                        if ($page < Paginator::PAGINATOR_PAGES_LIMIT || $page >= $pages-Paginator::PAGINATOR_PAGES_LIMIT || ($page >= $start-1 && $page <= $start+1)) {
                            // page item
                            $pageItemClass = '';
                            $pageItemClasses = [];
                            if ($this->_config['page-item-class']) {
                                $pageItemClasses[] = $this->_config['page-item-class'];
                            }
                            if ($this->_config['page-item'] == $this->_config['active-element'] && $page == $start) {
                                $pageItemClasses[] = $this->_config['active-element-class'];
                            }
                            if (count($pageItemClasses)) {
                                $pageItemClass = ' class="'.implode(' ', $pageItemClasses).'"';
                            }
                            
                            $pageItemOpenTag = '<'.$this->_config['page-item'].$pageItemClass.'>';
                            $pageItemCloseTag = '</'.$this->_config['page-item'].'>';
                            $pageItem = '<'.$this->_config['page-item'].'>';
                            
                            // page link
                            $pageLinkClass = '';
                            $pageLinkClasses = [];
                            if ($this->_config['page-link-class']) {
                                $pageLinkClasses[] = $this->_config['page-link-class'];
                            }
                            if ($this->_config['page-link'] == $this->_config['active-element'] && $page == $start) {
                                $pageLinkClasses[] = $this->_config['active-element-class'];
                            }
                            if (count($pageLinkClasses)) {
                                $pageLinkClass = ' class="'.implode(' ', $pageLinkClasses).'"';
                            }
                            
                            $pageLinkHref = ' href="'.$preLink.($page).$postLink.'"';
                            
                            $pageLinkOpenTag = '<'.$this->_config['page-link'].$pageLinkClass.$pageLinkHref.'>';
                            $pageLinkCloseTag = '</'.$this->_config['page-link'].'>';
                            
                            $this->_pagination .= $pageItemOpenTag
                                .$pageLinkOpenTag
                                    .$page
                                .$pageLinkCloseTag
                            .$pageItemCloseTag;
                        } else {
                            $this->_pagination .= '%';
                        }
                    }
                }
            }
            
            // single pages
            if (in_array('all', $this->_mode)) {
                if ($pages > 0) {
                    for ($page = 1; $page <= $pages; $page++) {
                        if ($page == $start) {
                            $this->_pagination .= '<li'.($this->_config['active-element'] == 'li' ? ' class="'.$this->_config['active-element-class'].'"' : '').'><a href="'.$preLink.($page).$postLink.'"'.($this->_config['active-element'] == 'a' ? ' class="'.$this->_config['active-element-class'].'"' : '').'>'.($page).'</a></li>';
                        } else {
                            $this->_pagination .= '<li'.($this->_config['page-item-class'] ? ' class="'.$this->_config['page-item-class'].'"' : '').'><a href="'.$preLink.($page).$postLink.'">'.($page).'</a></li>';
                        }
                    }
                }
            }

            // next page    
            if ($pages > ($start) && in_array('next', $this->_mode)) {
                $this->_pagination .= '<li><a href="'.$preLink.($start+1).$postLink.'">'.$this->_nextLabel.'</a></li>';
            }

                        
            $this->_pagination = preg_replace('/[%]+/', '<li><span class="separator">...</span></li>', $this->_pagination);
            $this->_pagination .= '</'.$this->_config['inner-wrapper'].'>';
            $this->_pagination .= ($this->_config['outer-wrapper'] ? '</'.$this->_config['outer-wrapper'].'>' : '');
            return $this->_pagination;
        } else {
            return '';
        }
    }
}

