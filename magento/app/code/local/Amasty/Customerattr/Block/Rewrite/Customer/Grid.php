<?php
/**
* @author Amasty
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Rewrite_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid
{
    protected $_attributeCollection;
    
    public function __construct()
    {
        parent::__construct();
        
//        $this->_attributeCollection = Mage::getModel('eav/entity_attribute')->getCollection();
        $this->_attributeCollection = Mage::getModel('customer/attribute')->getCollection();
        $this->_attributeCollection->addFieldToFilter('is_user_defined', 1);
        // 'is_filterable_in_search' used to setting "Show on Manage Customers Grid"
        $this->_attributeCollection->addFieldToFilter('is_filterable_in_search', 1); 
        $this->_attributeCollection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
        $this->_attributeCollection->getSelect()->order('sorting_order');
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id');
            
        foreach ($this->_attributeCollection as $attribute)
        {
            $collection->addAttributeToSelect($attribute->getAttributeCode());
        }
        
        $collection
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');
        $this->setCollection($collection);
        
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        
        // will backup action column
        $actionColumn = $this->_columns['action'];
        unset($this->_columns['action']);
        
        
        foreach ($this->_attributeCollection as $attribute)
        {
            if ($inputType = $attribute->getFrontend()->getInputType())
            {
                switch ($inputType)
                {
                    case 'date':
                        $this->addColumn($attribute->getAttributeCode(), array(
                            'header'    => __($attribute->getFrontend()->getLabel()),
                            'type'      => 'date',
                            'align'     => 'center',
                            'index'     => $attribute->getAttributeCode(),
                            'gmtoffset' => true
                        ));
                        break;
                    case 'text':
                    case 'textarea':
                        $this->addColumn($attribute->getAttributeCode(), array(
                            'header'    => __($attribute->getFrontend()->getLabel()),
                            'index'     => $attribute->getAttributeCode(),
                            'filter'    => 'adminhtml/widget_grid_column_filter_text',
                            'sortable'  => true,
                        ));
                        break;
                    case 'select':
                        $options = array();
                        foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                        {
                            $options[$option['value']] = $option['label'];
                        }
                        $this->addColumn($attribute->getAttributeCode(), array(
                            'header'    =>  __($attribute->getFrontend()->getLabel()),
                            'index'     =>  $attribute->getAttributeCode(),
                            'type'      =>  'options',
                            'options'   =>  $options,
                        ));
                        break;
                    case 'multiselect':
                        $options = array();
                        foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                        {
                            $options[$option['value']] = $option['label'];
                        }
                        $this->addColumn($attribute->getAttributeCode(), array(
                            'header'    =>  __($attribute->getFrontend()->getLabel()),
                            'index'     =>  $attribute->getAttributeCode(),
                            'type'      =>  'options',
                            'options'   =>  $options,
                            'renderer'  => 'amcustomerattr/adminhtml_renderer_multiselect',
                            'filter'    => 'amcustomerattr/adminhtml_filter_multiselect',
                        ));
                        break;
                }
            }
        }
        
        // restoring action column
        $this->_columns[] = $actionColumn;
        
        return $this;
    }

}