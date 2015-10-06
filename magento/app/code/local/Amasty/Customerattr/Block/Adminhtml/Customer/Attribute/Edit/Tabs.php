<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Customer_Attribute_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('product_attribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('catalog')->__('Attribute Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('main', array(
            'label'     => Mage::helper('catalog')->__('Properties'),
            'title'     => Mage::helper('catalog')->__('Properties'),
            'content'   => $this->getLayout()->createBlock('amcustomerattr/adminhtml_customer_attribute_edit_tab_main')->toHtml(),
            'active'    => true
        ));

        $model = Mage::registry('entity_attribute');

        $this->addTab('labels', array(
            'label'     => Mage::helper('catalog')->__('Manage Label / Options'),
            'title'     => Mage::helper('catalog')->__('Manage Label / Options'),
            'content'   => $this->getLayout()->createBlock('amcustomerattr/adminhtml_customer_attribute_edit_tab_options')->toHtml(),
        ));
        
        if ('multiselectimg' == $model->getFrontendInput() || 'selectimg' == $model->getFrontendInput())
        {
            $this->addTab('images', array(
                'label'     => Mage::helper('catalog')->__('Manage Option Images'),
                'title'     => Mage::helper('catalog')->__('Manage Option Images'),
                'content'   => $this->getLayout()->createBlock('amcustomerattr/adminhtml_customer_attribute_edit_tab_images')->toHtml(),
            ));
        }

        return parent::_beforeToHtml();
    }

}
