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

class Sunpop_News_Block_Newsitem extends Mage_Core_Block_Template
{
    protected $_pagesCount = null;
    protected $_currentPage = null;
    protected $_itemsOnPage = 10;
    protected $_pages;

    protected function _construct()
    {
        $this->_currentPage = $this->getRequest()->getParam('page');
        if (!$this->_currentPage) {
            $this->_currentPage=1;
        }

        $itemsPerPage = (int)Mage::getStoreConfig('clnews/comments/commentsperpage');
        if ($itemsPerPage > 0) {
            $this->_itemsOnPage = $itemsPerPage;
        }
    }

    protected function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            $newsitem = $this->getNewsItem();

            if ($newsitem==null) {
                return;
            }
            $showBreadcrumbs = (int)Mage::getStoreConfig('clnews/news/showbreadcrumbs');
            if ($showBreadcrumbs && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))) {
                $breadcrumbs->addCrumb('home',
                    array(
                    'label'=>Mage::helper('clnews')->__('Home'),
                    'title'=>Mage::helper('clnews')->__('Go to Home Page'),
                    'link'=>Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)
                    ));

                $breadcrumbs->addCrumb('news',
                    array(
                    'label'=>Mage::helper('clnews')->__(Mage::getStoreConfig('clnews/news/title')),
                    'title'=>Mage::helper('clnews')->__('Return to %s', Mage::helper('clnews')->__('News')),
                    'link'=> Mage::getUrl(Mage::helper('clnews')->getRoute())
                    ));

                if ($category = $newsitem->getCategory()) {
                    $breadcrumbs->addCrumb('category',
                    array(
                    'label' => $category->getTitle(),
                    'title' => Mage::helper('clnews')->__('Return to %s', $category->getTitle()),
                    'link' => Mage::getUrl(Mage::helper('clnews')->getRoute()).'category/'.$category->getUrlKey().Mage::helper('clnews')->getNewsitemUrlSuffix()
                        ));
                }

                $breadcrumbs->addCrumb('item',
                    array(
                    'label'=>$newsitem->getTitle(),
                    'title'=>$newsitem->getTitle()
                    ));
            }

            $head->setTitle($newsitem->getTitle());
            if ($newsitem->getMetaKeywords()!='') {
                $head->setKeywords($newsitem->getMetaKeywords());
            } else {
                $head->setKeywords(Mage::getStoreConfig('clnews/news/metakeywords'));
            }
            if ($newsitem->getMetaDescription()!='') {
                $head->setDescription($newsitem->getMetaDescription());
            } else {
                $head->setDescription(Mage::getStoreConfig('clnews/news/metadescription'));
            }
        }
    }

    public function getNewsItem()
    {
        return Mage::registry('newsitem');
    }

    public function getComments()
    {
        $newsitem = $this->getNewsItem();

        $collection = Mage::getModel('clnews/comment')->getCollection()
            ->addNewsFilter($newsitem->getNewsId())
            ->addApproveFilter(Sunpop_News_Helper_Data::APPROVED_STATUS)
            ->setOrder('created_time ', 'asc');
        $this->_pagesCount = ceil($collection->getSize()/$this->_itemsOnPage);
        for ($i=1; $i<=$this->_pagesCount;$i++) {
            $this->_pages[] = $i;
        }
        $this->setLastPageNum($this->_pagesCount);

        $collection->setPageSize($this->_itemsOnPage);
        $collection->setCurPage($this->_currentPage);

        return $collection;
    }

    public function getRequireLogin()
    {
        return Mage::getStoreConfig('clnews/comments/need_login');
    }

    public function getImageUrl()
    {
        $newsitem = $this->getNewsItem();
        $image = Mage::getBaseUrl('media'). DS .$newsitem->getImage();
        return $image;
    }

    public function getBackUrl()
    {
        $newsitem = $this->getNewsItem();
        if ($category = $newsitem->getCategory()) {
            return Mage::getUrl(Mage::helper('clnews')->getRoute()).'category/'.$category->getUrlKey().Mage::helper('clnews')->getNewsitemUrlSuffix();
        } else {
            return $this->getUrl(Mage::helper('clnews')->getRoute());
        }
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

    public function getPageUrl($page, $id)
    {
        if ($category = $this->getRequest()->getParam('category')) {
            return $this->getUrl('*/newsitem/view', array('category' => $category, 'id' => $id, 'page' => $page));
        } else {
            return $this->getUrl('*/newsitem/view', array('id' => $id, 'page' => $page));
        }
    }

    public function getNextPageUrl()
    {
        $page = $this->_currentPage+1;
        return $this->getPageUrl($page);
    }

    public function getPreviousPageUrl($id)
    {
        $page = $this->_currentPage-1;
        return $this->getPageUrl($page, $id);
    }

    public function getPages()
    {
        return $this->_pages;
    }

    public function getPrintLogoUrl ()
    {
        // load html logo
        $logo = Mage::getStoreConfig('sales/identity/logo_html');
        if (!empty($logo)) {
            $logo = 'sales/store/logo_html/' . $logo;
        }

        // load default logo
        if (empty($logo)) {
            $logo = Mage::getStoreConfig('sales/identity/logo');
            if (!empty($logo)) {
                // prevent tiff format displaying in html
                if (strtolower(substr($logo, -5)) === '.tiff' || strtolower(substr($logo, -4)) === '.tif') {
                    $logo = '';
                }
                else {
                    $logo = 'sales/store/logo/' . $logo;
                }
            }
        }

        // buld url
        if (!empty($logo)) {
            $logo = Mage::getStoreConfig('web/unsecure/base_media_url') . $logo;
        }
        else {
            $logo = '';
        }

        return $logo;
    }

    public function getPrintLogoText()
    {
        return Mage::getStoreConfig('sales/identity/address');
    }

    public function getLang()
    {
        if (!$this->hasData('lang')) {
            $this->setData('lang', substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2));
        }
        return $this->getData('lang');
    }

    public function getAbsoluteFooter()
    {
        return Mage::getStoreConfig('design/footer/absolute_footer');
    }

    public function getBodyClass()
    {
        return $this->_getData('body_class');
    }

    public function getFullImageSize($item)
    {
        $width_max = Mage::getStoreConfig('clnews/news/fulldescr_image_max_width');
        $height_max = Mage::getStoreConfig('clnews/news/fulldescr_image_max_height');
        if (Mage::getStoreConfig('clnews/news/resize_to_max') == 1) {
            $width = $width_max;
            $height = $height_max;
        } else {
            $imageObj = new Varien_Image(Mage::getBaseDir('media') . DS . $item->getImageFullContent());
            $original_width = $imageObj->getOriginalWidth();
            $original_height = $imageObj->getOriginalHeight();
            if ($original_width > $width_max) {
                $width = $width_max;
            } else {
                $width = $original_width;
            }
            if ($original_height > $height_max) {
                $height = $height_max;
            } else {
                $height = $original_height;
            }
        }
        if ($item->getFullWidthResize()): $width = $item->getFullWidthResize(); else: $width; endif;
        if ($item->getFullHeightResize()): $height = $item->getFullHeightResize(); else: $height; endif;

        return array('width' => $width, 'height' => $height);
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        return $html.'<div align="right" class="clcopyright">&copy Developed by <a href="http://commerce-lab.com/">Sunpop</a></div>';
    }
}
