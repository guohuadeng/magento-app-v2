<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Customer_Relation_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('product_relation_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('catalog')->__('Relation Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('main', array(
            'label'     => Mage::helper('catalog')->__('General'),
            'title'     => Mage::helper('catalog')->__('General'),
            'content'   => $this->getLayout()->createBlock('amcustomerattr/adminhtml_customer_relation_edit_tab_main')->toHtml(),
            'active'    => true
        ));

        return parent::_beforeToHtml();
    }

}
