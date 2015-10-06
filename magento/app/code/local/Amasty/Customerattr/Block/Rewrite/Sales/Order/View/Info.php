<?php
/**
* @author Amasty
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Rewrite_Sales_Order_View_Info extends Mage_Adminhtml_Block_Sales_Order_View_Info
{
    public function getCustomerAccountData()
    {
        $accountData = parent::getCustomerAccountData();
        
        if ($customerId = $this->getOrder()->getCustomerId())
        {
            $customer   = Mage::getModel('customer/customer')->load($customerId);
            
            $attributes = Mage::getModel('customer/attribute')->getCollection();
            
            $alias = Mage::helper('amcustomerattr')->getProperAlias($attributes->getSelect()->getPart('from'), 'eav_attribute');
            $attributes->addFieldToFilter($alias . 'is_user_defined', 1);
            $attributes->addFieldToFilter($alias . 'entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
            
            $alias = Mage::helper('amcustomerattr')->getProperAlias($attributes->getSelect()->getPart('from'), 'customer_eav_attribute');
            $attributes->addFieldToFilter($alias . 'on_order_view', 1); 
            $attributes->getSelect()->order($alias . 'sorting_order');
            
            foreach ($attributes as $attribute)
            {
                $label       = $this->__($attribute->getFrontend()->getLabel());
                $value       = '';
                $currentData = '';
                if ($inputType = $attribute->getFrontend()->getInputType())
                {
                    $currentData = $customer->getData($attribute->getAttributeCode());
                }
                
                if ($inputType == 'select' || $inputType == 'selectimg' || $inputType == 'multiselect' || $inputType == 'multiselectimg') 
                {
                    // getting values translations
                    $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setAttributeFilter($attribute->getId())
                        ->setStoreFilter($this->getOrder()->getStoreId(), false)
                        ->load();
                    foreach ($valuesCollection as $item) {
                        $values[$item->getId()] = $item->getValue();
                    }
                    
                    // applying translations
                    $options = $attribute->getSource()->getAllOptions(false, true);
                    foreach ($options as $i => $option)
                    {
                        if (isset($values[$option['value']]))
                        {
                            $options[$i]['label'] = $values[$option['value']];
                        }
                    }
                    // applying translations
                    
                    if (false !== strpos($inputType, 'multi'))
                    {
                        $currentData = explode(',', $currentData);
                        foreach ($options as $option)
                        {
                            if (in_array($option['value'], $currentData))
                            {
                                $value .= $option['label'] . ', ';
                            }
                        }
                        if ($value)
                        {
                            $value = substr($value, 0, -2);
                        }
                    } else 
                    {
                        foreach ($options as $option)
                        {
                            if ($option['value'] == $currentData)
                            {
                                $value = $option['label'];
                            }
                        }
                    }
                    
                } elseif ($inputType == 'date') {
                    $format = Mage::app()->getLocale()->getDateFormat(
                        Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
                    );
                    $value = Mage::getSingleton('core/locale')->date($currentData, Zend_Date::ISO_8601, null, false)->toString($format);
                } elseif ($inputType == 'boolean') {
                    $value = $currentData ? 'Yes' : 'No';
                } elseif ('file' == $attribute->getTypeInternal()) {
                    if ($currentData) {
                        $downloadUrl = Mage::helper('amcustomerattr')->getAttributeFileUrl($currentData, true);
                        $fileName = Mage::helper('amcustomerattr')->cleanFileName($currentData);
                        $value = '<a href="'. $downloadUrl .'">' . $fileName[3] . '</a>';
                    } else {
                        $value = 'No Uploaded File';
                    }
                } else {
                    $value = $currentData;
                }

                if ($value) {
                    $accountData[] = array('label' => $label, 'value' => $value);
                }
            }
        }
        
        return $accountData;
    }
}