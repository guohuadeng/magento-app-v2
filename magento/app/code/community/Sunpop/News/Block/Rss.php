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

class Sunpop_News_Block_Rss extends Mage_Rss_Block_Abstract
{
    protected function _toHtml()
    {
        $rssObj = Mage::getModel('rss/rss');

        $data = array('title' => 'News',
            'description' => 'News',
            'link'        => $this->getUrl('clnews/rss'),
            'charset'     => 'UTF-8',
            'language'    => Mage::getStoreConfig('general/locale/code')
            );

        $rssObj->_addHeader($data);

        $collection = Mage::getModel('clnews/news')->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->setOrder('created_time ', 'desc');

        $categoryId = $this->getRequest()->getParam('category');

        if ($categoryId && $category = Mage::getSingleton('clnews/category')->load($categoryId)) {
            $collection->addCategoryFilter($category->getUrlKey());
        }

        $collection->setPageSize((int)Mage::getStoreConfig('clnews/rss/posts'));
        $collection->setCurPage(1);

        if ($collection->getSize()>0) {
            foreach ($collection as $item) {
                $data = array(
                            'title'         => $item->getTitle(),
                            'link'          => $this->getUrl("clnews/newsitem/view", array("id" => $item->getId())),
                            'description'   => $item->getShortContent(),
                            'lastUpdate'    => strtotime($item->getNewsTime()),
                            );

                $rssObj->_addEntry($data);
            }
        } else {
             $data = array('title' => Mage::helper('clnews')->__('Cannot retrieve the news'),
                    'description' => Mage::helper('clnews')->__('Cannot retrieve the news'),
                    'link'        => Mage::getUrl(),
                    'charset'     => 'UTF-8',
                );
             $rssObj->_addHeader($data);
        }

        return $rssObj->createRssXml();
    }
}
