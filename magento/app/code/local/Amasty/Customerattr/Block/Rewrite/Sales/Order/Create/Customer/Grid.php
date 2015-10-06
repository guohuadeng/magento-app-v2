<?php
/**
* @author Amasty
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Rewrite_Sales_Order_Create_Customer_Grid extends Mage_Adminhtml_Block_Sales_Order_Create_Customer_Grid
{
    protected $_attributeCollection = null;
    
    public function __construct()
    {
        parent::__construct();
        
        if (Mage::getStoreConfig('amcustomerattr/general/select_grid'))
        {
            $this->_attributeCollection = Mage::getModel('customer/attribute')->getCollection();
            $alias = Mage::helper('amcustomerattr')->getProperAlias($this->_attributeCollection->getSelect()->getPart('from'), 'eav_attribute');
            $this->_attributeCollection->addFieldToFilter($alias . 'is_user_defined', 1);
            $this->_attributeCollection->addFieldToFilter($alias . 'entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
            
            $alias = Mage::helper('amcustomerattr')->getProperAlias($this->_attributeCollection->getSelect()->getPart('from'), 'customer_eav_attribute');            
            $this->_attributeCollection->addFieldToFilter($alias . 'is_filterable_in_search', 1); // 'is_filterable_in_search' used to setting "Show on Manage Customers Grid"
            $this->_attributeCollection->getSelect()->order($alias . 'sorting_order');
        }
    }
    
    protected function _prepareCollection()
    {
        if (!$this->_attributeCollection)
        {
            return parent::_prepareCollection();
        }
        
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at');
            
        foreach ($this->_attributeCollection as $attribute)
        {
            $collection->addAttributeToSelect($attribute->getAttributeCode());
        }
        
        $collection->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_regione', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
            ->joinField('store_name', 'core/store', 'name', 'store_id=store_id', null, 'left');

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        
        if (!$this->_attributeCollection)
        {
            return $this;
        }
        
        foreach ($this->_attributeCollection as $attribute)
        {
            if ($inputType = $attribute->getFrontend()->getInputType())
            {
                switch ($inputType)
                {
                    case 'date':
                        $this->addColumn($attribute->getAttributeCode(), array(
                            'header'    => $this->__($attribute->getFrontend()->getLabel()),
                            'type'      => 'date',
                            'align'     => 'center',
                            'index'     => $attribute->getAttributeCode(),
                            'gmtoffset' => true
                        ));
                        break;
                    case 'text':
                    case 'textarea':
                        $this->addColumn($attribute->getAttributeCode(), array(
                            'header'    => $this->__($attribute->getFrontend()->getLabel()),
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
                            'header'    =>  $this->__($attribute->getFrontend()->getLabel()),
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
                            'header'    =>  $this->__($attribute->getFrontend()->getLabel()),
                            'index'     =>  $attribute->getAttributeCode(),
                            'type'      =>  'options',
                            'options'   =>  $options,
                            'renderer'  => 'amcustomerattr/adminhtml_renderer_multiselect',
                            'filter'    => 'amcustomerattr/adminhtml_filter_multiselect',
                        ));
                        break;
                    case 'boolean':
                        $options = array(0 => 'No', 1 => 'Yes');
                        $this->addColumn($attribute->getAttributeCode(), array (
                            'header'       => $this->__($attribute->getFrontend()->getLabel()),
                            'index'        => $attribute->getAttributeCode(),
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                            'renderer'     => 'amcustomerattr/adminhtml_renderer_boolean',
                        ));
                        break;
                }
                if ('file' == $attribute->getTypeInternal()) {
                    $this->addColumn($attribute->getAttributeCode(), array (
                        'header'       => $this->__($attribute->getFrontend()->getLabel()),
                        'index'        => $attribute->getAttributeCode(),
                        'align'        => 'center',
                        'renderer'     => 'amcustomerattr/adminhtml_renderer_file',
                    ));
                }
            }
        }
        
        return $this;
    }
}