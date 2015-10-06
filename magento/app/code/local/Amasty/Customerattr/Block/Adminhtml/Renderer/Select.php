<?php
/**
* @author Amasty
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Renderer_Select extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $hlp = Mage::helper('amcustomerattr');
        $res = $row->getData($this->getColumn()->getIndex());
        if ($res == '2')
            $res = $hlp->__('Yes');
        else if ($res == '1')
            $res = $hlp->__('No');
        else
            $res = $hlp->__('Pending');

        return $res;
    }
}