<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Customer_Attribute_Edit_Tab_Depends extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amcustomerattr/attribute/depends.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete'),
                    'class' => 'delete delete-option'
                )));

        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Add Option'),
                    'class' => 'add',
                    'id'    => 'add_new_option_button'
                )));
        return parent::_prepareLayout();
    }
    
    protected function _getValues($option, $defaultValues, $inputType) {
       $value = array(); 
       if (in_array($option->getId(), $defaultValues)) {
            $value['checked'] = 'checked="checked"';
        } else {
            $value['checked'] = '';
        }
        $value['intype'] = $inputType;
        $value['id'] = $option->getId();
        $value['sort_order'] = $option->getSortOrder();
        foreach ($this->getStores() as $store) {
            $storeValues = $this->getStoreOptionValues($store->getId());
            if (isset($storeValues[$option->getId()])) {
                $value['store'.$store->getId()] = htmlspecialchars($storeValues[$option->getId()]);
            }
            else {
                $value['store'.$store->getId()] = '';
            }
        }
        return $value;
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getStores()
    {
        $stores = $this->getData('stores');
        if (is_null($stores)) {
            $stores = Mage::getModel('core/store')
                ->getResourceCollection()
                ->setLoadDefault(true)
                ->load();
            $this->setData('stores', $stores);
        }
        return $stores;
    }

    public function getOptionValues()
    {
        $attributeType = $this->getAttributeObject()->getFrontendInput();
        $defaultValues = $this->getAttributeObject()->getDefaultValue();
        if ($attributeType == 'select' || $attributeType == 'multiselect' || $attributeType == 'selectimg' || $attributeType == 'multiselectimg') {
            $defaultValues = explode(',', $defaultValues);
        } else {
            $defaultValues = array();
        }

        switch ($attributeType) {
            case 'select':
                $inputType = 'radio';
                break;
            case 'multiselect':
                $inputType = 'checkbox';
                break;
            default:
                $inputType = '';
                break;
        }
        $customer = Mage::getModel('customer/customer');
        $customerForm = Mage::getModel('customer/form');
        $customerForm->setEntity($customer)
                     ->setFormCode('adminhtml_customer')
                     ->initDefaultValues();
        $attributes = $customerForm->getAttributes();
        $groupValues = array();
        foreach ($attributes as $attribute) {
             if ($attribute->getAttributeCode() == 'group_id') {
                $groupValues = $attribute->getSource()->getAllOptions(true, true);
             }
        }
        
        $values = $this->getData('option_values');
        if (is_null($values)) {
            $values = array();
            $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($this->getAttributeObject()->getId())
                ->setPositionOrder('desc', true)
                ->load();
            
            if ('selectgroup' != $this->getAttributeObject()->getTypeInternal()){
                foreach ($optionCollection as $option) {
                   $value = $this->_getValues($option,$defaultValues,$inputType);
                   $values[] = new Varien_Object($value);
                }
            }
            else{
               foreach($groupValues as $key=>$val){
                    $value = array();
                    foreach ($optionCollection as $option) {
                        if($val['value'] == $option->getGroupId()){
                             $value = $this->_getValues($option,$defaultValues,$inputType);
                        }
                    }
                    $value['group_name'] = $val['label'];
                    $value['group_id'] = $val['value'];
                    $values[] = new Varien_Object($value);
               } 
            }
            $this->setData('option_values', $values);
        }

        return $values;
    }

    public function getLabelValues()
    {
        $values = array();
        $values[0] = $this->getAttributeObject()->getFrontend()->getLabel();
        // it can be array and cause bug
        $frontendLabel = $this->getAttributeObject()->getFrontend()->getLabel();
        if (is_array($frontendLabel)) {
            $frontendLabel = array_shift($frontendLabel);
        }
        $storeLabels = $this->getAttributeObject()->getStoreLabels();
        foreach ($this->getStores() as $store) {
            if ($store->getId() != 0) {
                $values[$store->getId()] = isset($storeLabels[$store->getId()]) ? $storeLabels[$store->getId()] : '';
            }
        }

        return $values;
    }

    public function getStoreOptionValues($storeId)
    {
        $values = $this->getData('store_option_values_'.$storeId);
        if (is_null($values)) {
            $values = array();
            $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($this->getAttributeObject()->getId())
                ->setStoreFilter($storeId, false)
                ->load();
            foreach ($valuesCollection as $item) {
                $values[$item->getId()] = $item->getValue();
            }
            $this->setData('store_option_values_'.$storeId, $values);
        }
        return $values;
    }

    public function getAttributeObject()
    {
        return Mage::registry('entity_attribute');
    }

}
