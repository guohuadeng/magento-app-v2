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
/**
 * Quick Search Controller
 */
class Sunpop_RestConnect_SearchController extends Mage_Core_Controller_Front_Action {
	protected function _getSession() {
		return Mage::getSingleton ( 'catalog/session' );
	}
	
	public function indexAction() {
		$order = ($this->getRequest ()->getParam ( 'order' )) ? ($this->getRequest ()->getParam ( 'order' )) : 'entity_id';
		$dir = ($this->getRequest ()->getParam ( 'dir' )) ? ($this->getRequest ()->getParam ( 'dir' )) : 'desc';
		$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
		$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 20;
		
		$query = Mage::helper ( 'catalogsearch' )->getQuery ();
		/* @var $query Mage_CatalogSearch_Model_Query */
		$query->setStoreId ( Mage::app ()->getStore ()->getId () );
		if ($query->getQueryText () != '') {
			if (Mage::helper ( 'catalogsearch' )->isMinQueryLength ()) {
				$query->setId ( 0 )->setIsActive ( 1 )->setIsProcessed ( 1 );
			} else {
				if ($query->getId ()) {
					$query->setPopularity ( $query->getPopularity () + 1 );
				} else {
					$query->setPopularity ( 1 );
				}
				
				if ($query->getRedirect ()) {
					$query->save ();
					$this->getResponse ()->setRedirect ( $query->getRedirect () );
					return;
				} else {
					$query->prepare ();
				}
			}
			
			Mage::helper ( 'catalogsearch' )->checkNotes ();
			// $collection = Mage::getModel ( "catalogsearch/query" )->getResultCollection ();
			$result = $query->getResultCollection ();
			
			//pages
			$result->setPageSize($limit);				
			$result->setCurPage($page);

			//sort
			//$ud = 'ASC' | 'DESC'
			$result->addAttributeToSort($order,$dir);				
			$result->load();				
			$lastpagenumber = $result->getLastPageNumber();
				
				
			$i = 1;
// 			foreach ( $collection as $o ) {
// 				echo "<strong>Product Order:" . $i . "</strong><br/>";
// 				echo "Product Entity_Id: " . $o->getId () . "<br/>";
// 				echo "Product Price: " . $o->getPrice () . "<br/>";
// 				$i ++;
// 				echo "----------------------------------<br/>";
// 			}
			// $this->loadLayout ();
			// $this->_initLayoutMessages ( 'catalog/session' );
			// $this->_initLayoutMessages ( 'checkout/session' );
			// $this->renderLayout ();
			$baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
		    $currentCurrency = Mage::app ()->getStore ()->getCurrentCurrencyCode ();
			foreach($result as $product){
			    $product = Mage::getModel ( 'catalog/product' )->load (  $product->getId () );
			    $productlist [] = array (
        			'entity_id' => $product->getId (),
        			'sku' => $product->getSku (),
        			'name' => $product->getName (),
        			'news_from_date' => $product->getNewsFromDate (),
        			'news_to_date' => $product->getNewsToDate (),
        			'special_from_date' => $product->getSpecialFromDate (),
        			'special_to_date' => $product->getSpecialToDate (),
        			'image_url' => $product->getImageUrl (),
        			'url_key' => $product->getProductUrl (),
        			'regular_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
        			'final_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getSpecialPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
        			'symbol' => Mage::app ()->getLocale ()->currency ( Mage::app ()->getStore ()->getCurrentCurrencyCode () )->getSymbol ()
    			);
    			$i ++;
			}
			$returndata['productlist'] = $productlist;
			$returndata['lastpagenumber'] = $lastpagenumber;
			echo Mage::helper('core')->jsonEncode($returndata);
			if (! Mage::helper ( 'catalogsearch' )->isMinQueryLength ()) {
				$query->save ();
			}
		} else {
			// $this->_redirectReferer ();
		}
	}

	public function getfilterAction() {
		//http://domainname/restconnect/search/getfilter/categoryid/1
		//categoryid
		$categoryid = $this->getRequest()->getParam('categoryid');
		$layer = Mage::getModel("catalog/layer");
		if($categoryid){
			$rootCategory=Mage::getModel('catalog/category')->load($categoryid);
		}else{
			$rootCategory=Mage::getModel('catalog/category')->load(Mage::app()->getStore()->getRootCategoryId());
		}
		$layer->setCurrentCategory($rootCategory);
		$attributes = $layer->getFilterableAttributes();
	
		$this->_filterableAttributesExists=array();
		foreach ($attributes as $attribute) {
			$datas = '';
			$collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
			->setPositionOrder('asc')
			->setAttributeFilter($attribute->getSource()->getAttribute()->getId())
			->setStoreFilter($attribute->getSource()->getAttribute()->getStoreId())
			->load();
			 
			$attributeType = $attribute->getSource()->getAttribute()->getFrontendInput();
			$defaultValues = $attribute->getSource()->getAttribute()->getDefaultValue();
			$_labels = $attribute->getSource()->getAttribute()->getStoreLabels();
	
			if ($attributeType == 'select' || $attributeType == 'multiselect') {
				$defaultValues = explode(',', $defaultValues);
			} else {
				$defaultValues = array();
			}
			$options = $collection->getData();
			$datas['label'] = $_labels;
			foreach($options as $option){
				if (in_array($option['option_id'], $defaultValues)){
					$option['isdefault'] =1;
				}
				$datas[] = $option;
			}
			 
			$this->_filterableAttributes[$attribute->getAttributeCode()]=$datas;
		}
		krsort($this->_filterableAttributes);
		echo Mage::helper('core')->jsonEncode($this->_filterableAttributes);
	
	}	
	
	public function testAction() {
		$query = Mage::helper ( 'catalogSearch' )->getQuery ();
		$searcher = Mage::getSingleton ( 'catalogsearch/advanced' )->addFilters ( array (
				'name' => $query->getQueryText (),
				'description' => $query->getQueryText () 
		) );
		// $obj = new stdClass ();
		// $obj->query = $query->getQueryText ();
		// $obj->results = $searcher->getProductCollection (); // nothing returned
		$result = $searcher->getProductCollection()->getData()/* ->getItems () */;
		//$mod = Mage::getModel ( 'catalog/product' );
		//echo $result;
		foreach ( $result as $product ) {
			//var_dump ( $product);
			// $product = Mage::getModel ( 'catalog/product' )->load ( $product ['entity_id'] );
			// $productlist [] = array (
			// 'entity_id' => $product->getId (),
			// 'sku' => $product->getSku (),
			// 'name' => $product->getName (),
			// 'news_from_date' => $product->getNewsFromDate (),
			// 'news_to_date' => $product->getNewsToDate (),
			// 'special_from_date' => $product->getSpecialFromDate (),
			// 'special_to_date' => $product->getSpecialToDate (),
			// 'image_url' => $product->getImageUrl (),
			// 'url_key' => $product->getProductUrl (),
			// 'regular_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
			// 'final_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getSpecialPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
			// 'symbol' => Mage::app ()->getLocale ()->currency ( Mage::app ()->getStore ()->getCurrentCurrencyCode () )->getSymbol ()
			// );
		}
		var_dump($result);
	}
}
