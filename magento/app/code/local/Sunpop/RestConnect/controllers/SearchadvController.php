<?php
/**
 * Catalog Search Controller
 */
class Sunpop_RestConnect_SearchadvController extends Mage_Core_Controller_Front_Action {
	protected function _getSession() {
		return Mage::getSingleton ( 'catalog/session' );
	}

	public function indexAction() {
		//http://domainname/restconnect/searchadv/?name=aaa&description=bbbb&short_description=ccc&sku=123&price=2to6&tax_class_id=1,2,3
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
			$query = Mage::helper ( 'catalogSearch' )->getQuery ();
			$farray = array();//构建一个addFilters 数组参数
			if($_GET['name']) $farray['name'] = $_GET['name'];
			if($_GET['description']) $farray['description'] = $_GET['description'];
			if($_GET['short_description']) $farray['short_description'] = $_GET['short_description'];
			if($_GET['sku']) $farray['sku'] = $_GET['sku'];
			if($_GET['price']) {
				$price = explode("to", $_GET['price']);
				$from = $price[1] ? $price[0] : '0';
				$to = !$price[1] ? $price[0] : $price[1];
				$farray['price'] = array(
						'from' => $from,
						'to'   => $to
				);
			}
			if(!empty($_GET['tax_class_id']) || $_GET['tax_class_id'] == '0') {				
				$farray['tax_class_id'] = explode(",", $_GET['tax_class_id']);
				if(empty($farray['tax_class_id'])) $farray['tax_class_id'] = array($_GET['tax_class_id']);
			}


			$searcher = Mage::getSingleton ( 'catalogsearch/advanced' )->addFilters ( $farray );

			$result = $searcher->getProductCollection();
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
			echo json_encode($productlist);
	}

}
