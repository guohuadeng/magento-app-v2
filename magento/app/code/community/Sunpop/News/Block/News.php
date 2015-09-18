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

class Sunpop_News_Block_News extends Sunpop_News_Block_Abstract
{
    protected function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            // show breadcrumbs
            $moduleName = $this->getRequest()->getModuleName();
            $showBreadcrumbs = (int)Mage::getStoreConfig('clnews/news/showbreadcrumbs');
            if ($showBreadcrumbs && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) && ($moduleName=='clnews')) {
                $breadcrumbs->addCrumb('home',
                    array(
                    'label'=>Mage::helper('clnews')->__('Home'),
                    'title'=>Mage::helper('clnews')->__('Go to Home Page'),
                    'link'=> Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)));
                $newsBreadCrumb = array(
                    'label'=>Mage::helper('clnews')->__(Mage::getStoreConfig('clnews/news/title')),
                    'title'=>Mage::helper('clnews')->__('Return to ' .Mage::helper('clnews')->__('News')),
                    );
                if ($this->getCategoryKey()) {
                    $newsBreadCrumb['link'] = Mage::getUrl(Mage::helper('clnews')->getRoute());
                }
                $breadcrumbs->addCrumb('news', $newsBreadCrumb);

                if ($this->getCategoryKey()) {
                    $categories = Mage::getModel('clnews/category')
                        ->getCollection()
                        ->addFieldToFilter('url_key', $this->getCategoryKey())
                        ->setPageSize(1);
                    $category = $categories->getFirstItem();
                    $breadcrumbs->addCrumb('category',
                        array(
                        'label'=>$category->getTitle(),
                        'title'=>Mage::helper('clnews')->__('Go to Home Page'),
                        ));
                }
            }

            if ($moduleName=='clnews') {
                // set default meta data
                $head->setTitle(Mage::getStoreConfig('clnews/news/metatitle'));
                $head->setKeywords(Mage::getStoreConfig('clnews/news/metakeywords'));
                $head->setDescription(Mage::getStoreConfig('clnews/news/metadescription'));

                // set category meta data if defined
                $currentCategory = $this->getCurrentCategory();
                if ($currentCategory!=null) {
                    if ($currentCategory->getTitle()!='') {
                        $head->setTitle($currentCategory->getTitle());
                    }
                    if ($currentCategory->getMetaKeywords()!='') {
                        $head->setKeywords($currentCategory->getMetaKeywords());
                    }
                    if ($currentCategory->getMetaDescription()!='') {
                        $head->setDescription($currentCategory->getMetaDescription());
                    }
                }
            }
        }
    }

    public function getShortImageSize($item)
    {
        $width_max = Mage::getStoreConfig('clnews/news/shortdescr_image_max_width');
        $height_max = Mage::getStoreConfig('clnews/news/shortdescr_image_max_height');
        if (Mage::getStoreConfig('clnews/news/resize_to_max') == 1) {
            $width = $width_max;
            $height = $height_max;
        } else {
            $imageObj = new Varien_Image(Mage::getBaseDir('media') . DS . $item->getImageShortContent());
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
        if ($item->getShortWidthResize()): $width = $item->getShortWidthResize(); else: $width; endif;
        if ($item->getShortHeightResize()): $height = $item->getShortHeightResize(); else: $height; endif;

        return array('width' => $width, 'height' => $height);
    }
}
