<?php
class Sunpop_Duplicate_Model_Product extends Mage_Catalog_Model_Product
{

	public function getMediaConfig()
	{
		return Mage::getSingleton('catalog/product_media_config');
	}
	
	/**
	 * Create duplicate
	 *
	 * @return Mage_Catalog_Model_Product
	 */
	public function duplicate()
	{
		$this->getWebsiteIds();
		$this->getCategoryIds();
	
		/* @var $newProduct Mage_Catalog_Model_Product */
	
		$newProduct = Mage::getModel('catalog/product')->setData($this->getData())
		->setIsDuplicate(true)
		->setOriginalId($this->getId())
		->setSku(null)
		->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
		->setCreatedAt(null)
		->setUpdatedAt(null)
		->setId(null)
		->setStoreId(Mage::app()->getStore()->getId());
	
		if(Mage::getStoreConfig('duplicate/duplicate/duplicate',Mage::app()->getStore())){
			$newProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
		}
		Mage::dispatchEvent(
				'catalog_model_product_duplicate',
				array('current_product' => $this, 'new_product' => $newProduct)
		);
	
		/* Prepare Related*/
		$data = array();
		$this->getLinkInstance()->useRelatedLinks();
		$attributes = array();
		foreach ($this->getLinkInstance()->getAttributes() as $_attribute) {
			if (isset($_attribute['code'])) {
				$attributes[] = $_attribute['code'];
			}
		}
		foreach ($this->getRelatedLinkCollection() as $_link) {
			$data[$_link->getLinkedProductId()] = $_link->toArray($attributes);
		}
		$newProduct->setRelatedLinkData($data);
	
		/* Prepare UpSell*/
		$data = array();
		$this->getLinkInstance()->useUpSellLinks();
		$attributes = array();
		foreach ($this->getLinkInstance()->getAttributes() as $_attribute) {
			if (isset($_attribute['code'])) {
				$attributes[] = $_attribute['code'];
			}
		}
		foreach ($this->getUpSellLinkCollection() as $_link) {
			$data[$_link->getLinkedProductId()] = $_link->toArray($attributes);
		}
		$newProduct->setUpSellLinkData($data);
	
		/* Prepare Cross Sell */
		$data = array();
		$this->getLinkInstance()->useCrossSellLinks();
		$attributes = array();
		foreach ($this->getLinkInstance()->getAttributes() as $_attribute) {
			if (isset($_attribute['code'])) {
				$attributes[] = $_attribute['code'];
			}
		}
		foreach ($this->getCrossSellLinkCollection() as $_link) {
			$data[$_link->getLinkedProductId()] = $_link->toArray($attributes);
		}
		$newProduct->setCrossSellLinkData($data);
	
		/* Prepare Grouped */
		$data = array();
		$this->getLinkInstance()->useGroupedLinks();
		$attributes = array();
		foreach ($this->getLinkInstance()->getAttributes() as $_attribute) {
			if (isset($_attribute['code'])) {
				$attributes[] = $_attribute['code'];
			}
		}
		foreach ($this->getGroupedLinkCollection() as $_link) {
			$data[$_link->getLinkedProductId()] = $_link->toArray($attributes);
		}
		$newProduct->setGroupedLinkData($data);
	
		$newProduct->save();
	
		$this->getOptionInstance()->duplicate($this->getId(), $newProduct->getId());
		$this->getResource()->duplicate($this->getId(), $newProduct->getId());
	
		// TODO - duplicate product on all stores of the websites it is associated with
		/*if ($storeIds = $this->getWebsiteIds()) {
		foreach ($storeIds as $storeId) {
		$this->setStoreId($storeId)
		->load($this->getId());
	
		$newProduct->setData($this->getData())
		->setSku(null)
		->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
		->setId($newId)
		->save();
		}
		}*/
		return $newProduct;
	}

}