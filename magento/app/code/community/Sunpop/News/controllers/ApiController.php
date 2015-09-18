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

class Sunpop_News_ApiController extends Mage_Core_Controller_Front_Action
{
    // http://domainname/clnews/api/category
    public function categoryAction(){
    	$collection = Mage::getModel('clnews/category')->getCollection()
    				->addStoreFilter(Mage::app()->getStore()->getId());
    	$datas = '';
    	foreach($collection as $category){	
    		$datas['categorylist'][] = $category->getData();
    	}
    	echo Mage::helper('core')->jsonEncode($datas);
    }
    
    // categoryid
    public function articleAction(){
    	$dir = ($this->getRequest ()->getParam ( 'dir' )) ? ($this->getRequest ()->getParam ( 'dir' )) : 'asc';
    	$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
    	$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 20;
    	$categoryid = $this->getRequest()->getParam('categoryid');
    	
    	$collection = Mage::getModel('clnews/news')->getCollection()
    				->addEnableFilter(1)
    				->addStoreFilter(Mage::app()->getStore()->getId());
    	//var_dump(get_class_methods($collection));exit;
    	
    	if (isset($categoryid) && !empty($categoryid)) {
    		$tableName = Mage::getSingleton('core/resource')->getTableName('clnews_news_category');
    		$collection->getSelect()->join($tableName, 'main_table.news_id = ' . $tableName . '.news_id','category_id');
    		$collection->getSelect()->where($tableName . '.category_id =?', $categoryid);
    	}
    	
    	$collection->setOrder('news_time',$dir);
    	$collection->setPageSize($limit);
    	$collection->setCurPage($page);
    	$datas = '';
	    $mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
    	foreach($collection as $article){
    		$data = $article->getData();
    		if(isset($data['full_path_document']) && strlen($data['full_path_document'])>1){
    			$data['full_path_document'] = $mediaUrl.split('/media/', $data['full_path_document'])[1];
    		}
    		if(isset($data['image_short_content']) && strlen($data['image_short_content'])>1){
    			$data['image_short_content'] = $mediaUrl.$data['image_short_content'];
    		}
    		if(isset($data['image_full_content']) && strlen($data['image_full_content'])>1){
    			$data['image_full_content'] = $mediaUrl.$data['image_full_content'];
    		}
    		
    		$datas['articlelist'][]= $data;
    	}
    	if($collection->getLastPageNumber() < $page) $datas['articlelist'] = '';
    	$collection->load();

    	echo Mage::helper('core')->jsonEncode($datas);
    }
}
