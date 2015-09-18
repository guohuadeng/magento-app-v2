<?php
/**
 * Sunpop Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Sunpop License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://commerce-lab.com/LICENSE.txt
 *
 * @category   Sunpop
 * @package    Sunpop_News
 * @copyright  Copyright (c) 2012 Sunpop Co. (http://commerce-lab.com)
 * @license    http://commerce-lab.com/LICENSE.txt
 */

class Sunpop_News_Block_Abstract extends Mage_Core_Block_Template
{
    protected $_pagesCount = null;
    protected $_currentPage = null;
    protected $_itemsOnPage = 10;
    protected $_itemsLimit;
    protected $_pages;
    protected $_latestItemsCount = 2;
    protected $_showFlag = 0;

    protected function _construct()
    {
        $this->_currentPage = $this->getRequest()->getParam('page');
        if (!$this->_currentPage) {
            $this->_currentPage=1;
        }

        $itemsPerPage = (int)Mage::getStoreConfig('clnews/news/itemsperpage');
        if ($itemsPerPage > 0) {
            $this->_itemsOnPage = $itemsPerPage;
        }

        $itemsLimit = (int)$this->getData('itemslimit');
        if ($itemsLimit==null) {
            $itemsLimit = (int)Mage::getStoreConfig('clnews/news/itemslimit');
        }
        if ($itemsLimit > 0) {
            $this->_itemsLimit = $itemsLimit;
        } else {
            $this->_itemsLimit = null;
        }

        $latestItemsCount = (int)Mage::getStoreConfig('clnews/news/latestitemscount');
        if ($latestItemsCount > 0) {
            $this->_latestItemsCount = $latestItemsCount;
        }
    }

    public function getCategoryKey()
    {
        $category = $this->getData('category');
        if ($category==null) {
            $category = $this->getRequest()->getParam('category');
        }
        if (!preg_match('/^[0-9A-Za-z\-\_]+$/i', $category)) {
            return null;
        }
        return $category;
    }

    public function getCategory()
    {
        $category = $this->getData('category');
        if ($category==null) {
            $category = $this->getRequest()->getParam('category');
        }
        if (!preg_match('/^[0-9A-Za-z\-\_]+$/i', $category)) {
            return null;
        }
        return $category;
    }

    public function getNewsItems()
    {
        $this->_showFlag = 1;
        $collection = Mage::getModel('clnews/news')->getCollection();

        $category = $this->getCategoryKey();
        if ($category!=null) {
            $catCollection = Mage::getModel('clnews/category')->getCollection()
                ->addFieldToFilter('url_key', $category)
                ->addStoreFilter(Mage::app()->getStore()->getId());
            $categoryId = $catCollection->getData();
            if ($categoryId[0]['category_id']) {
                $tableName = Mage::getSingleton('core/resource')->getTableName('clnews_news_category');
                $collection->getSelect()->join($tableName, 'main_table.news_id = ' . $tableName . '.news_id','category_id');
                $collection->getSelect()->where($tableName . '.category_id =?', $categoryId[0]['category_id']);
            }
        } else {
            $collection->addStoreFilter(Mage::app()->getStore()->getId());
        }
        if ($tag = $this->getRequest()->getParam('q')) {
            $collection = Mage::getModel('clnews/news')->getCollection()->setOrder('news_time', 'desc');
            if (count(Mage::app()->getStores()) > 1) {
                $tableName = Mage::getSingleton('core/resource')->getTableName('clnews_news_store');
                $collection->getSelect()->join($tableName, 'main_table.news_id = ' . $tableName . '.news_id','store_id');
                $collection->getSelect()->where('('. $tableName . '.store_id = '. Mage::app()->getStore()->getId(). ' OR store_id = 0)');
            }
            $tag = urldecode($tag);
            $collection->getSelect()->where("tags LIKE '%". $tag . "%'");
        }

        $collection
            ->addEnableFilter(1)
            ->addFieldToFilter('publicate_from_time', array('or' => array(
                0 => array('date' => true, 'to' => date('Y-m-d H:i:s')),
                1 => array('is' => new Zend_Db_Expr('null'))),
                ), 'left')
            ->addFieldToFilter('publicate_to_time', array('or' => array(
                0 => array('date' => true, 'from' => date('Y-m-d H:i:s')),
                1 => array('is' => new Zend_Db_Expr('null'))),
                ), 'left')
            ->setOrder('news_time ', 'desc');
        if ($this->_itemsLimit!=null && $this->_itemsLimit<$collection->getSize()) {
            $this->_pagesCount = ceil($this->_itemsLimit/$this->_itemsOnPage);
        } else {
            $this->_pagesCount = ceil($collection->getSize()/$this->_itemsOnPage);
        }
        for ($i=1; $i<=$this->_pagesCount;$i++) {
            $this->_pages[] = $i;
        }
        $this->setLastPageNum($this->_pagesCount);

        $offset = $this->_itemsOnPage*($this->_currentPage-1);
        if ($this->_itemsLimit!=null) {
            $_itemsCurrentPage = $this->_itemsLimit - $offset;
            if ($_itemsCurrentPage > $this->_itemsOnPage) {
                $_itemsCurrentPage = $this->_itemsOnPage;
            }
            $collection->getSelect()->limit($_itemsCurrentPage, $offset);
        } else {
            $collection->getSelect()->limit($this->_itemsOnPage, $offset);
        }

        foreach ($collection as $item) {
            $comments = Mage::getModel('clnews/comment')->getCollection()
                ->addNewsFilter($item->getNewsId())
                ->addApproveFilter(Sunpop_News_Helper_Data::APPROVED_STATUS);
            $item->setCommentsCount(count($comments));
        }
        return $collection;
    }

    public function getLatestNewsItems()
    {
        $collection = Mage::getModel('clnews/news')->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId());
        $collection->setPageSize($this->_latestItemsCount);
        $collection
            ->addEnableFilter(1)
            ->addFieldToFilter('publicate_from_time', array('or' => array(
                0 => array('date' => true, 'to' => date('Y-m-d H:i:s')),
                1 => array('is' => new Zend_Db_Expr('null'))),
                ), 'left')
            ->addFieldToFilter('publicate_to_time', array('or' => array(
                0 => array('date' => true, 'from' => date('Y-m-d H:i:s')),
                1 => array('is' => new Zend_Db_Expr('null'))),
                ), 'left')
            ->setOrder('news_time ', 'desc');
        return $collection;
    }

    public function getCategories()
    {
        $collection = Mage::getModel('clnews/category')->getCollection()
        ->addStoreFilter(Mage::app()->getStore()->getId())
        ->setOrder('sort_id', 'asc');

        foreach ($collection as $item) {
            $item->setUrl(Mage::getUrl(Mage::helper('clnews')->getRoute()).'category/'.$item->getUrlKey().Mage::helper('clnews')->getNewsitemUrlSuffix());
        }
        return $collection;
    }

    public function getTopLink()
    {
        $route = Mage::helper('clnews')->getRoute();
        $title = Mage::helper('clnews')->__(Mage::getStoreConfig('clnews/news/title'));
        if ($this->getParentBlock() && $this->getParentBlock()!=null) {
            $this->getParentBlock()->addLink($title, $route, $title, true, array(), 15, null, 'class="top-link-news"');
        }
    }

    public function getItemUrl($itemId) {
        return $this->getUrl(Mage::helper('clnews')->getRoute().'/newsitem/view', array('id' => $itemId));
    }

    public function isFirstPage()
    {
        if ($this->_currentPage==1) {
            return true;
        }
        return false;
    }

    public function isLastPage()
    {
        if ($this->_currentPage==$this->_pagesCount) {
            return true;
        }
        return false;
    }

    public function isPageCurrent($page)
    {
        if ($page==$this->_currentPage) {
            return true;
        }
        return false;
    }

    public function getPageUrl($page)
    {
        if (Mage::app()->getRequest()->getModuleName()==Mage::helper('clnews')->getRoute()) {
            if ($category = $this->getCategoryKey()) {
                return $this->getUrl('*', array('category' => $category, 'page' => $page));
            } else {
                return $this->getUrl('*', array('page' => $page));
            }
        } else {
            if (strstr( Mage::helper("core/url")->getCurrentUrl(), '?')) {
                $sign = '&';
            } else {
                $sign = '?';
            }
            if (strstr( Mage::helper("core/url")->getCurrentUrl(),'page=' )) {
                return preg_replace('(page=[0-9]+)', 'page='.$page, Mage::helper("core/url")->getCurrentUrl());
            } else {
                return Mage::helper("core/url")->getCurrentUrl().$sign.'page='.$page;
            }
        }
    }

    public function getNextPageUrl()
    {
        $page = $this->_currentPage+1;
        return $this->getPageUrl($page);
    }

    public function getPreviousPageUrl()
    {
        $page = $this->_currentPage-1;
        return $this->getPageUrl($page);
    }

    public function getPages()
    {
        return $this->_pages;
    }

    public function getCurrentCategory()
    {
        if ($this->getCategoryKey()) {
            $categories = Mage::getModel('clnews/category')
                ->getCollection()
                ->addFieldToFilter('url_key', $this->getCategoryKey())
                ->setPageSize(1);
            $category = $categories->getFirstItem();
            return $category;
        }
        return null;
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        if ($this->_showFlag==1) {
            $html = $html.'<div align="right" class="clcopyright">&copy Developed by <a href="http://commerce-lab.com/">Sunpop</a></div>';
        }
        return $html;
    }

    public function getCategoryByNews($id)
    {
        $data = Mage::getModel('clnews/category')->getCategoryByNewsId($id);
        $data = new Varien_Object($data);
        $collection = Mage::getModel('clnews/category')->getCollection()
        ->addStoreFilter(Mage::app()->getStore()->getId());
        if ($data->getData('0/category_id')!= NULL) {
            $collection->getSelect()->where('main_table.category_id =' . $data->getData('0/category_id'));
            $category = $collection->getFirstItem();
            return $category;
        } else {
            $category = $collection->getFirstItem();
            return $category->setData('title','');
        }
    }
}
