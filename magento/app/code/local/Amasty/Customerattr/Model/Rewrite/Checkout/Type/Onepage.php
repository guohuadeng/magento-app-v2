<?php
class Amasty_Customerattr_Model_Rewrite_Checkout_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    public function saveBilling($data, $customerAddressId)
    {
        if (isset($data['amcustomerattr']))
        {
            // checking unique attributes
            $checkUnique = array();
            $nameGroupAttribute = '';
            $idGroupSelect = '';
            $collection = Mage::getModel('eav/entity_attribute')->getCollection();
            
            $alias = Mage::helper('amcustomerattr')->getProperAlias($collection->getSelect()->getPart('from'), 'eav_attribute');
            $collection->addFieldToFilter($alias . 'is_user_defined', 1);
            $collection->addFieldToFilter($alias . 'entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
            
            foreach ($collection as $attribute)
            {
                if ($attribute->getIsUnique())
                {
                    $translations = $attribute->getStoreLabels();
                    if (isset($translations[Mage::app()->getStore()->getId()]))
                    {
                        $attributeLabel = $translations[Mage::app()->getStore()->getId()];
                    } else 
                    {
                        $attributeLabel = $attribute->getFrontend()->getLabel();
                    }
                    $checkUnique[$attribute->getAttributeCode()] = $attributeLabel;
                }
            }
            
            $collection = Mage::getModel('customer/attribute')->getCollection();
            $alias = Mage::helper('amcustomerattr')->getProperAlias($collection->getSelect()->getPart('from'), 'eav_attribute');
            $collection->addFieldToFilter($alias . 'is_user_defined', 1);
            $collection->addFieldToFilter($alias . 'entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
            foreach ($collection as $attribute)
            {
                if ('selectgroup' == $attribute->getTypeInternal()){
                    $nameGroupAttribute = $attribute->getAttributeCode();
                }
            }
            foreach ($data['amcustomerattr'] as $attributeCode => $attributeValue){
                 if ($attributeCode == $nameGroupAttribute) {
                    $idGroupSelect = $attributeValue;
                }
            }
            if ($idGroupSelect) {
                $option = Mage::getModel('eav/entity_attribute_option')->load($idGroupSelect);
                if ($option && $option->getGroupId()) {
                    $customer = Mage::getModel('customer/customer');
                    $customer->setGroupId($option->getGroupId());
                }
            }
            
            if ($checkUnique) {
                foreach ($checkUnique as $attributeCode => $attributeLabel) {
                    //skip empty values
                    if (!$data['amcustomerattr'][$attributeCode]) {
                        continue;
                    }
                    $customerCollection = Mage::getResourceModel('customer/customer_collection');
                    $customerCollection->addAttributeToFilter($attributeCode, array('eq' => $data['amcustomerattr'][$attributeCode]));
                    if ($customerId = Mage::getSingleton('customer/session')->getCustomer()->getId()) {
                        $mainAlias = ( false !== strpos($customerCollection->getSelect()->__toString(), 'AS `e') ) ? 'e' : 'main_table';
                        $customerCollection->getSelect()->where($mainAlias . '.entity_id != ?', $customerId);
                    }
                    if ($customerCollection->getSize() > 0) {
                        $result = array(
                            'error'     => 1,
                            'message'   => Mage::helper('amcustomerattr')->__('Please specify different value for "%s" attribute. Customer with such value already exists.', $attributeLabel),
                        );
                        return $result;
                    }
                }
            }
            Mage::getSingleton('checkout/session')->setAmcustomerattr($data['amcustomerattr']);
        }
        return parent::saveBilling($data, $customerAddressId);
    }
}