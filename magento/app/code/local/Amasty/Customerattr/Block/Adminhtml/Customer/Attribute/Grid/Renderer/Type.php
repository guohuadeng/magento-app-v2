<?php
/**
* @author Amasty Team
* @copyright Copyright (c) Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Customer_Attribute_Grid_Renderer_Type extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $html = '';
        switch ($row->getFrontendInput()) {
            case 'text':
                $html = Mage::helper('amcustomerattr')->__('Text Field');
            break;
            case 'textarea':
                $html = Mage::helper('amcustomerattr')->__('Text Area');
            break;
            case 'date':
                $html = Mage::helper('amcustomerattr')->__('Date');
            break;
            case 'multiselect':
                $html = Mage::helper('amcustomerattr')->__('Multiple Select');
            break;
            case 'multiselectimg':
                $html = Mage::helper('amcustomerattr')->__('Multiple Checkbox Select with Images');
            break;
            case 'select':
                $html = Mage::helper('amcustomerattr')->__('Dropdown');
            break;
            case 'boolean':
                $html = Mage::helper('amcustomerattr')->__('Yes/No');
            break;
            case 'selectimg':
                $html = Mage::helper('amcustomerattr')->__('Single Radio Select with Images');
            break;
            case 'selectgroup':
                $html = Mage::helper('amcustomerattr')->__('Customer Group Selector');
            break;
            case 'statictext':
                $html = Mage::helper('amcustomerattr')->__('Static Text');
            break;
            case 'file':
                $html = Mage::helper('amcustomerattr')->__('Single File Upload');
            break;
        }
        return $html;
    }
}
