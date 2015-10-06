<?php
class Amasty_Customerattr_Model_Rewrite_Customer_Entity_Customer extends Mage_Customer_Model_Entity_Customer
{
    public function loadByAttribute(Mage_Customer_Model_Customer $customer, $attributeValue, $attribute)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('e' => $this->getEntityTable()), array($this->getEntityIdField()))
            ->joinLeft(array('attr' => $this->getEntityTable() . '_' . $attribute->getBackendType()), 'attr.entity_id = e.entity_id')
            //->where('email=?', $email);
            ->where('attr.attribute_id=:attribute_id')
            ->where('attr.value=:attribute_value');
        if ($id = $this->_getReadAdapter()->fetchOne($select, array('attribute_id' => $attribute->getId(), 'attribute_value' => $attributeValue))) {
            $this->load($customer, $id);
        }
        else {
            $customer->setData(array());
        }
        if ($customer->getSharingConfig()->isWebsiteScope()) {
            if (!$customer->hasData('website_id')) {
                //Mage::throwException(Mage::helper('customer')->__('Customer website ID must be specified when using the website scope.'));
            }
            $select->where('website_id=?', (int)$customer->getWebsiteId());
        }
        return $this;
    }
}