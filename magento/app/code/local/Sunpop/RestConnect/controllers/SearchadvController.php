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
class Sunpop_RestConnect_SearchadvController extends Mage_Core_Controller_Front_Action {
	protected function _getSession() {
		return Mage::getSingleton ( 'catalog/session' );
	}

	public function getfieldAction(){
		//$andor1,$andor2 值是 'AND' 或 'OR' 默认 AND
		//$is_searchable,$is_visible_in_advanced_search,$used_for_sort_by 值是 0 或 1
		//$where = $is_searchable_where .' '. $andor1 .' '. $is_visible_in_advanced_search_where .' '. $andor2 .' '. $used_for_sort_by_where;
		//$andor1 = $this->getRequest()->getParam('andor1') !== null ? $this->getRequest()->getParam('andor1') : 'and';
		$storeid = $this->getRequest()->getParam('storeid') ? $this->getRequest()->getParam('storeid') : Mage::app()->getStore()->getStoreId();
		$andor2 = $this->getRequest()->getParam('andor2') !== null ? $this->getRequest()->getParam('andor2') : 'and';
	
		//$is_searchable = $this->getRequest()->getParam('is_searchable') !== null ? $this->getRequest()->getParam('is_searchable') : 1;
		$is_visible_in_advanced_search = $this->getRequest()->getParam('is_visible_in_advanced_search') !== null ? $this->getRequest()->getParam('is_visible_in_advanced_search') : 1;
		$used_for_sort_by = $this->getRequest()->getParam('used_for_sort_by') !== null ? $this->getRequest()->getParam('used_for_sort_by') : 1;
	
		//$is_searchable_where = 'additional_table.is_searchable = ' . $is_searchable;
	
		$is_visible_in_advanced_search_where = 'is_visible_in_advanced_search = ' . $is_visible_in_advanced_search;
	
		$used_for_sort_by_where = 'additional_table.used_for_sort_by = ' . $used_for_sort_by;
	
		//$where = $is_searchable_where .' '. $andor1 .' '. $is_visible_in_advanced_search_where .' '. $andor2 .' '. $used_for_sort_by_where;
		$where = $is_visible_in_advanced_search_where;
		$attributes = Mage::getResourceModel('catalog/product_attribute_collection')
					->setOrder('position', 'asc')
					->addVisibleFilter();
		$attributes->getSelect()->where(sprintf('(%s)',$where));
		$attributes->load();
		$orderby = 'ASC';
		foreach ($attributes as $attribute) {
			$datas = '';
			$collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
			->setAttributeFilter($attribute->getSource()->getAttribute()->getId())
			->setStoreFilter($attribute->getSource()->getAttribute()->getStoreId());
			
			$collection->getSelect()->order('main_table.sort_order '.$orderby);
			$collection->load();
			$attributeType = $attribute->getSource()->getAttribute()->getFrontendInput();
			$defaultValues = $attribute->getSource()->getAttribute()->getDefaultValue();
			$_labels = $attribute->getSource()->getAttribute()->getStoreLabels();
			//$_label = $_labels[$storeid];
			$_label = $_labels[$storeid] ? $_labels[$storeid] : $attribute->getSource()->getAttribute()->getFrontendLabel();
	
			if ($attributeType == 'select' || $attributeType == 'multiselect') {
				$defaultValues = explode(',', $defaultValues);
			} else {
				$defaultValues = array();
			}
			
			$options = $collection->getData();

			$optionid = 0;
			$datas['label'] = $_label;
			$datas['attributeType'] = $attributeType;
			foreach($options as $option){
				if (in_array($option['option_id'], $defaultValues)){
					$option['isdefault'] =1;
				}
				$options[$optionid] = $option;
				$optionid++;
			}

			$datas['key'] = $attribute->getAttributeCode();
			$datas['code'] = $attribute->getAttributeCode();
			$datas['position'] = $attribute->getPosition();
			$datas['label'] = $_label;
			$datas['attributeType'] = $attributeType;
			$datas['attributeValue'] = $options;	
			 
			$this->_searchableAttributes[$attribute->getAttributeCode()]=$datas;
		}
		//krsort($this->_searchableAttributes);
		echo Mage::helper('core')->jsonEncode($this->_searchableAttributes);
			
	}
	public function indexAction() {
		//http://domainname/restconnect/searchadv/index/name/aaa/description/bbbb/short_description/ccc/sku/123/price/2to6/tax_class_id/1,2,3
		//		/pagesize/6/
		//?color=5  5 是 option_id
		//'name' => string 'name' (length=4)
		//'description' => string 'des' (length=3)
		//'short_description' => string 'sdes' (length=4)
		//'sku' => string 'sku' (length=3)
		//'price' =>
		//	array (size=2)
		//	'from' => string '0' (length=1)
		//	'to' => string '2' (length=1)
		//'tax_class_id' =>
		//	array (size=1)
		//	0 => string '0' (length=1)
		//'color' =>
		//	array (size=1)
		//	0 => string '5' (length=1)
		
		$order = ($this->getRequest ()->getParam ( 'order' )) ? ($this->getRequest ()->getParam ( 'order' )) : 'entity_id';
		$dir = ($this->getRequest ()->getParam ( 'dir' )) ? ($this->getRequest ()->getParam ( 'dir' )) : 'desc';
		$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
		$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 20;
	
		$farray = array();//构建一个addFilters 数组参数
		if($this->getRequest ()->getParam ( 'name' )) $farray['name'] = $this->getRequest ()->getParam ( 'name' );
		if($this->getRequest ()->getParam ( 'description' )) $farray['description'] = $this->getRequest ()->getParam ( 'description' );
		if($this->getRequest ()->getParam ( 'short_description' )) $farray['short_description'] = $this->getRequest ()->getParam ( 'short_description' );
		if($this->getRequest ()->getParam ( 'sku' )) $farray['sku'] = $this->getRequest ()->getParam ( 'short_description' );
		if($this->getRequest ()->getParam ( 'price' )) {
			$price = explode("to", $this->getRequest ()->getParam ( 'price' ));
			$from = $price[1] ? $price[0] : '0';
			$to = !$price[1] ? $price[0] : $price[1];
			$farray['price'] = array(
					'from' => $from,
					'to'   => $to
			);
		}
		if(!empty($this->getRequest ()->getParam ( 'tax_class_id' )) || $this->getRequest ()->getParam ( 'tax_class_id' ) == '0') {				
			$farray['tax_class_id'] = explode(",", $this->getRequest ()->getParam ( 'tax_class_id' ));
		}
		foreach ($this->getRequest ()->getParams() as $key => $value){
			if(!in_array($key, array('name','description','short_description','sku','price','tax_class_id'))){
				if(!empty($value) || $value == '0') {				
					$farray[$key] = explode(",", $value);
					if(count($farray[$key]) <= 1) $farray[$key] = $value;
				}
			}
		}
		$searcher = Mage::getSingleton ( 'catalogsearch/advanced' )->addFilters ( $farray );

		$result = $searcher->getProductCollection()
				->addAttributeToFilter ( 'status', 1 )
				->addAttributeToFilter ( 'visibility', array (
						'neq' => 1 
				) );
		if($categoryid = $this->getRequest()->getParam('categoryid')){
			$_category = Mage::getModel('catalog/category')->load($categoryid);
			$result->addCategoryFilter($_category);
		}
		//var_dump(get_class_methods($result));exit;
		//pages
		
		$result->setPageSize($limit);
		$result->setCurPage($page);
		//sort
		$result->addAttributeToSort($order,$dir);
		$result->load();
		
		$lastpagenumber = $result->getLastPageNumber();
		
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
	}

}
