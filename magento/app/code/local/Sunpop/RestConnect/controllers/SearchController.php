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
 * Catalog Search Controller
 */
class Sunpop_RestConnect_SearchController extends Mage_Core_Controller_Front_Action {
	protected function _getSession() {
		return Mage::getSingleton ( 'catalog/session' );
	}
	
	public function getfilterAction() {
		//http://domainname/restconnect/search/getfilter/categoryid/1/storeid/1
		//categoryid
		$storeid = $this->getRequest()->getParam('storeid') ? $this->getRequest()->getParam('storeid') : Mage::app()->getStore()->getStoreId();
		$categoryid = $this->getRequest()->getParam('categoryid');
 		$layer = Mage::getModel("catalog/layer");
 		if($categoryid){
        	$rootCategory=Mage::getModel('catalog/category')->load($categoryid); 
 		}else{
 			$rootCategory=Mage::getModel('catalog/category')->load(Mage::app()->getStore()->getRootCategoryId());
 		}
 		
 		if(!$rootCategory->is_active && !$rootCategory->is_anchor){ 
 			echo Mage::helper('core')->jsonEncode(false); 
 			return;
 		}
 		
        $layer->setCurrentCategory($rootCategory);  
        $attributes = $layer->getFilterableAttributes();  
        
       
        $this->_filterableAttributesExists=array();  
        foreach ($attributes as $attribute) {
        	$datas = '';
        	$collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
        	->setPositionOrder('asc')
        	->setAttributeFilter($attribute->getSource()->getAttribute()->getId())
        	->setStoreFilter($storeid)
        	->load();

        	$attributeType = $attribute->getSource()->getAttribute()->getFrontendInput();
        	$defaultValues = $attribute->getSource()->getAttribute()->getDefaultValue();
			
        	$_labels = $attribute->getSource()->getAttribute()->getStoreLabels();
			$_label = $_labels[$storeid] ? $_labels[$storeid] : $attribute->getSource()->getAttribute()->getFrontendLabel();
        	if ($attributeType == 'select' || $attributeType == 'multiselect') {
        		$defaultValues = explode(',', $defaultValues);
        	} else {
        		$defaultValues = array();
        	}
        	$options = $collection->getData();
			$datas['key'] = $attribute->getAttributeCode();
        	$datas['label'] = $_label;
        	$datas['attributeType'] = $attributeType;
        	foreach($options as $option){
	        	if (in_array($option['option_id'], $defaultValues)){
	        		$option['isdefault'] = 1;
	        	}
	        	$datas[] = $option;
        	}
        	
        	$this->_filterableAttributes[$attribute->getAttributeCode()]=$datas;
        }  
        krsort($this->_filterableAttributes);  
        echo Mage::helper('core')->jsonEncode($this->_filterableAttributes);
		
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
			$result = $query->getResultCollection ()
					->addAttributeToFilter ( 'status', 1 )
					->addAttributeToFilter ( 'visibility', array (
							'neq' => 1
					) );
			//pages
			$result->setPageSize($limit);
				
			$result->setCurPage($page);
			

			//sort
			//$ud = 'ASC' | 'DESC'
			$result->addAttributeToSort($order,$dir);

				
			$result->load();
				
			$lastpagenumber = $result->getLastPageNumber();
				
				
			$i = 1;
			$baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
		    $currentCurrency = Mage::app ()->getStore ()->getCurrentCurrencyCode ();
			foreach($result as $product){
			    $product = Mage::getModel ( 'catalog/product' )->load (  $product->getId () );

				$regular_price_with_tax = number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' );
				$final_price_with_tax = $product->getSpecialPrice ();
				if (!is_null($final_price_with_tax))	{
					$final_price_with_tax = number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getSpecialPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' );
					$discount = round (($regular_price_with_tax - $final_price_with_tax)/$regular_price_with_tax*100);
					$discount = $discount.'%';
					}
				else {
					$discount = null;
				}
				$productlist [] = array (
						'entity_id' => $product->getId (),
						'sku' => $product->getSku (),
						'name' => $product->getName (),
						'news_from_date' => $product->getNewsFromDate (),
						'news_to_date' => $product->getNewsToDate (),
						'special_from_date' => $product->getSpecialFromDate (),
						'special_to_date' => $product->getSpecialToDate (),
						'image_url' => $product->getImageUrl (),
						'image_thumbnail_url' => Mage::getModel ( 'catalog/product_media_config' )->getMediaUrl( $product->getThumbnail() ),
						'image_small_url' => Mage::getModel ( 'catalog/product_media_config' )->getMediaUrl( $product->getSmallImage() ),
								//also use getSmallImage() or getThumbnail()
						'url_key' => $product->getProductUrl (),
						'regular_price_with_tax' => $regular_price_with_tax,
						'final_price_with_tax' => $final_price_with_tax,
						'discount' => $discount,
						'symbol'=> Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol()
				);
			}
			$returndata['productlist'] = $productlist;
			if($this->getRequest ()->getParam ( 'page' ) > $result->getLastPageNumber()) $returndata['productlist'] = '';
			$returndata['lastpagenumber'] = $lastpagenumber;
			echo Mage::helper('core')->jsonEncode($returndata);
			if (! Mage::helper ( 'catalogsearch' )->isMinQueryLength ()) {
				$query->save ();
			}
		} else {
			// $this->_redirectReferer ();
		}
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
