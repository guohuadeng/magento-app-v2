<?php
/**
 * * NOTICE OF LICENSE
 * * This source file is subject to the Open Software License (OSL 3.0)
 *
 * Author: Ivan Deng
 * QQ: 300883
 * Email: 300883@qq.com
 * @copyright  Copyright (c) 2008-2015 Sunpop Ltd. (http://www.sunpop.cn)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Sunpop_RestConnect_StoreController extends Mage_Core_Controller_Front_Action {
	public function websiteInfoAction() {
		// Mage::app ()->getWebsites ();
		// Mage::app ()->getStores ();
		$basicinfo = array ();
		foreach ( Mage::app ()->getWebsites () as $sk=> $website ) {
			$basicinfo[$sk]['webside']['name']=$website->getName();
			$basicinfo[$sk]['webside']['id']=$website->getId();
			foreach ( $website->getGroups () as $key=> $group ) {
				$basicinfo [$sk]['webside'][$key]['store']=$group->getName();
				$basicinfo [$sk]['webside'][$key]['store_id']=$group->getGroupId ();
				$basicinfo [$sk]['webside'][$key]['root_category_id']=$group->getRootCategoryId ();
				$stores = $group->getStores ();
				foreach ( $stores as $oo =>$_store ) {
					$storelang= Mage::getStoreConfig('general/locale/code', $_store->getStoreId ());	
					$basicinfo [$sk]['webside'][$key]['view'][$oo] = array (
							'name' => $_store->getName (),
							'store_id' => $_store->getStoreId (),
							'store_url' => $_store->getUrl (),
							'store_code'=>$_store->getCode(),
							'sort_order' => $_store->getSortOrder(),
							'is_active' =>$_store->getIsActive(),
							'storelang' =>$storelang
					);
				}
			}
			
		}
		echo json_encode($basicinfo);
		// public function getStoresStructure($isAll = false, $storeIds = array(), $groupIds = array(), $websiteIds = array())
		// echo json_encode ( Mage::getSingleton ( 'adminhtml/system_store' )->getStoresStructure (TRUE) );
		// echo json_encode(Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true));
	}
	public function storeInfoAction(){
		$website_id = Mage::app()->getStore()->getWebsiteId();
		$store_id = Mage::app()->getStore()->getStoreId();		
		$website_name = Mage::app ()->getWebsite($website_id) -> getName();
		$group_id = Mage::app()->getStore()->getGroupId();
		$group_name = Mage::app ()->getGroup($group_id) -> getName();
		$storelang= Mage::getStoreConfig('general/locale/code', $store_id);		
		
		echo json_encode(array(
				'store_id'=>$store_id,
				'store_code'=>Mage::app()->getStore()->getCode(),
				'website_id'=>$website_id,
				'website_name'=>$website_name,
				'group_id'=>$group_id,
				'group_name'=>$group_name,
				'name'=>Mage::app()->getStore()->getName(),
				'sort_order' => Mage::app()->getStore()->getSortOrder(),
				'is_active'=>Mage::app()->getStore()->getIsActive(),
				'root_category_id' => Mage::app()->getStore()->getRootCategoryId(),
				//'url'=>Mage::app()->getStore()->getHomeUrl()
				'url'=> Mage::helper('core/url')->getHomeUrl(),
				'storelang'=> $storelang	
		));		
	}
	
}
