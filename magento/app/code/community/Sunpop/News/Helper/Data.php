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

class Sunpop_News_Helper_Data extends Mage_Core_Helper_Abstract
{
    const UNAPPROVED_STATUS = 0;
    const APPROVED_STATUS = 1;

    const XML_PATH_ENABLED          = 'news/news/enabled';
    const XML_PATH_TITLE            = 'news/news/title';
    const XML_PATH_MENU_LEFT        = 'news/news/menuLeft';
    const XML_PATH_MENU_RIGHT       = 'news/news/menuRoght';
    const XML_PATH_FOOTER_ENABLED   = 'news/news/footerEnabled';
    const XML_PATH_LAYOUT           = 'news/news/layout';

    public function isEnabled()
    {
        return Mage::getStoreConfig( self::XML_PATH_ENABLED );
    }

    public function isTitle()
    {
        return Mage::getStoreConfig( self::XML_PATH_TITLE );
    }

    public function isMenuLeft()
    {
        return Mage::getStoreConfig( self::XML_PATH_MENU_LEFT );
    }

    public function isMenuRight()
    {
        return Mage::getStoreConfig( self::XML_PATH_MENU_RIGHT );
    }

    public function isFooterEnabled()
    {
        return Mage::getStoreConfig( self::XML_PATH_FOOTER_ENABLED );
    }

    public function isLayout()
    {
        return Mage::getStoreConfig( self::XML_PATH_LAYOUT );
    }

    public function getUserName()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        return trim("{$customer->getFirstname()} {$customer->getLastname()}");
    }

    public function getRoute(){
        $route = Mage::getStoreConfig('clnews/news/route');
        if (!$route){
            $route = "clnews";
        }
        return $route;
    }

    public function getUserEmail()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        return $customer->getEmail();
    }

    public function getRssLink($categoryId)
    {
        if ($categoryId) {
            return Mage::getUrl('clnews/rss', array('category' => $categoryId));
        } else {
            return Mage::getUrl('clnews/rss');
        } 
    }

    public function getFileUrl($newsitem)
    {
        $file = Mage::getBaseDir('media'). 'clnews' . DS . $newsitem->getDocument();
        $file = str_replace(Mage::getBaseDir('media'), Mage::getBaseUrl('media'), $file);
        $file = str_replace('\\', '/', $file);
        return $file;
    }

    public function showAuthor()
    {
        return Mage::getStoreConfig('clnews/news/showauthorofnews');
    }

    public function showCategory()
    {
        return Mage::getStoreConfig('clnews/news/showcategoryofnews');
    }

    public function showDate()
    {
        return Mage::getStoreConfig('clnews/news/showdateofnews');
    }

    public function showTime()
    {
        return Mage::getStoreConfig('clnews/news/showtimeofnews');
    }

    public function enableLinkRoute()
    {
        return Mage::getStoreConfig('clnews/news/enablelinkrout');
    }

    public function getLinkRoute()
    {
        return Mage::getStoreConfig('clnews/news/linkrout');
    }
    public function getTagsAccess()
    {
        return Mage::getStoreConfig('clnews/news/tags');
    }

    public function getGoogleAccess()
    {
        return Mage::getStoreConfig('clnews/news/google');
    }

    public function getTwitterAccess()
    {
        return Mage::getStoreConfig('clnews/news/twitter');
    }

    public function getLinkedInAccess()
    {
        return Mage::getStoreConfig('clnews/news/linked_in');
    }

    public function getFaceBookAccess()
    {
        return Mage::getStoreConfig('clnews/news/facebook');
    }

    public function resizeImage($imageName, $width=NULL, $height=NULL, $imagePath=NULL)
    {
        $imagePath = str_replace("/", DS, $imagePath);
        $imagePathFull = Mage::getBaseDir('media') . DS . $imagePath . DS . $imageName;

        if($width == NULL && $height == NULL) {
            $width = 100;
            $height = 100;
        }
        $resizePath = $width . 'x' . $height;
        $resizePathFull = Mage::getBaseDir('media') . DS . $imagePath . DS . $resizePath . DS . $imageName;

        if (file_exists($imagePathFull) && !file_exists($resizePathFull)) {
            $imageObj = new Varien_Image($imagePathFull);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->resize($width,$height);
            $imageObj->save($resizePathFull);
        }

        $imagePath=str_replace(DS, "/", $imagePath);
        return Mage::getBaseUrl("media") . $imagePath . "/" . $resizePath . "/" . $imageName;
    }

    public function formatUrlKey($str)
    {
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');
    
        return $urlKey;
    }

    public function getNewsitemUrlSuffix()
    {
        return Mage::getStoreConfig('clnews/news/itemurlsuffix');
    }

    public function formatDate($date)
    {
        $date = Mage::helper('core')->formatDate($date, 'short', true);
        if (!Mage::helper('clnews')->showTime()) {
            $pos = strpos($date, ' ');
            $date = substr($date, 0, $pos);
        }
        return $date;
    }

    public function contentFilter($content)
    {
        $helper = Mage::helper('cms');
        $processor = $helper->getPageTemplateProcessor();
        $html = $processor->filter($content);
        return $html;
    }
}
