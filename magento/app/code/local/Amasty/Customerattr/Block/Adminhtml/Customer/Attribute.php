<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Customer_Attribute extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'amcustomerattr';
        $this->_controller = 'adminhtml_customer_attribute';
        $this->_headerText = Mage::helper('amcustomerattr')->__('Manage Customer Attributes');
        $this->_addButtonLabel = Mage::helper('amcustomerattr')->__('Add New Attribute');
        parent::__construct();
    }

}
