<?php
class Amasty_Customerattr_Model_Rewrite_Catalog_Product_Flat_Indexer extends Mage_Catalog_Model_Product_Flat_Indexer
{
    function updateAttribute($attributeCode, $store = null, $productIds = null)
    {
        $attribute = Mage::getModel('catalog/entity_attribute')
            ->loadByCode(Mage::getModel('eav/entity')->setType('customer')->getTypeId(), $attributeCode);
        if ($attribute && $attribute->getData('entity_type_id') == Mage::getModel('eav/entity')->setType('customer')->getTypeId())
        {
            return $this;
        }
        return parent::updateAttribute($attributeCode, $store, $productIds);
    }
}