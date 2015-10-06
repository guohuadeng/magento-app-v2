<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Customer_Relation extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'amcustomerattr';
        $this->_controller = 'adminhtml_customer_relation';
        $this->_headerText = Mage::helper('amcustomerattr')->__('Manage Attributes Relation');
        $this->_addButtonLabel = Mage::helper('amcustomerattr')->__('Add New Relation');
        parent::__construct();
    }

}
